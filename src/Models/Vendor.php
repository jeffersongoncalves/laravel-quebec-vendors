<?php

namespace JeffersonGoncalves\QuebecVendors\Models;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use JeffersonGoncalves\QuebecVendors\Enums\VendorIssue;
use JeffersonGoncalves\QuebecVendors\Enums\VendorRisk;
use JeffersonGoncalves\QuebecVendors\Enums\VendorRole;

/**
 * Someone you communicate personal information to: a service provider (s. 18.3) or a third party.
 *
 * @property string $name
 * @property string|null $document NEQ or foreign registration
 * @property VendorRole $role
 * @property string|null $services
 * @property list<string>|null $data_categories
 * @property string $location province (QC, ON...) or ISO 3166-1 alpha-2 country
 * @property Carbon|null $pia_completed_at privacy impact assessment before communicating outside Quebec (s. 17)
 * @property string|null $pia_reference
 * @property VendorRisk $risk
 * @property Carbon|null $dpa_signed_at
 * @property Carbon|null $dpa_expires_at
 * @property array<string, bool>|null $dpa_checklist
 * @property string|null $contact
 * @property Carbon|null $reviewed_at
 * @property Carbon|null $terminated_at
 * @property Carbon|null $data_returned_at
 * @property Carbon $created_at
 */
class Vendor extends Model
{
    protected $guarded = [];

    protected $attributes = [
        'location' => 'QC',
        'risk' => 'medium',
    ];

    public function getTable(): string
    {
        return config('quebec-vendors.table', 'quebec_vendors');
    }

    protected function casts(): array
    {
        return [
            'role' => VendorRole::class,
            'risk' => VendorRisk::class,
            'pia_completed_at' => 'date',
            'data_categories' => 'array',
            'dpa_checklist' => 'array',
            'dpa_signed_at' => 'date',
            'dpa_expires_at' => 'date',
            'reviewed_at' => 'datetime',
            'terminated_at' => 'datetime',
            'data_returned_at' => 'datetime',
        ];
    }

    /**
     * Gaps to fix. An ended relationship only owes the confirmation that the data was deleted or returned.
     *
     * @return list<VendorIssue>
     */
    public function issues(): array
    {
        if ($this->terminated_at !== null) {
            return $this->data_returned_at === null ? [VendorIssue::DataReturnPending] : [];
        }

        $issues = [];

        if ($this->role->needsAgreement()) {
            if ($this->dpa_signed_at === null) {
                $issues[] = VendorIssue::AgreementMissing;
            } elseif ($this->dpa_expires_at?->isPast()) {
                $issues[] = VendorIssue::AgreementExpired;
            }

            if ($this->dpa_signed_at !== null && $this->missingChecklistItems() !== []) {
                $issues[] = VendorIssue::AgreementIncomplete;
            }
        }

        if ($this->isOutsideQuebec() && $this->pia_completed_at === null) {
            $issues[] = VendorIssue::AssessmentMissing;
        }

        if ($this->nextReviewAt()->isPast()) {
            $issues[] = VendorIssue::ReviewDue;
        }

        return $issues;
    }

    /**
     * @return list<string> checklist keys from config('quebec-vendors.dpa_checklist') not ticked yet
     */
    public function missingChecklistItems(): array
    {
        $ticked = $this->dpa_checklist ?? [];
        $items = array_keys((array) config('quebec-vendors.dpa_checklist', []));

        if (! $this->isOutsideQuebec()) {
            $items = array_diff($items, ['outside_quebec_protection']);
        }

        return array_values(array_filter($items, fn (string $item): bool => ($ticked[$item] ?? false) !== true));
    }

    public function isOutsideQuebec(): bool
    {
        return ! in_array(strtoupper($this->location), array_map('strtoupper', (array) config('quebec-vendors.home', ['QC'])), true);
    }

    public function nextReviewAt(): Carbon
    {
        $every = (string) (config('quebec-vendors.review_every')[$this->risk->value] ?? '1 year');

        return ($this->reviewed_at ?? $this->created_at)->copy()->add(CarbonInterval::make($every));
    }

    public function markReviewed(): static
    {
        $this->forceFill(['reviewed_at' => now()])->save();

        return $this;
    }

    public function terminate(): static
    {
        $this->forceFill(['terminated_at' => now()])->save();

        return $this;
    }

    public function confirmDataReturned(): static
    {
        $this->forceFill(['data_returned_at' => now()])->save();

        return $this;
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereNull('terminated_at');
    }
}
