<?php

use Illuminate\Support\Facades\Artisan;
use JeffersonGoncalves\QuebecVendors\Enums\VendorIssue;
use JeffersonGoncalves\QuebecVendors\Enums\VendorRisk;
use JeffersonGoncalves\QuebecVendors\Enums\VendorRole;
use JeffersonGoncalves\QuebecVendors\Models\Vendor;

function fullChecklist(): array
{
    return array_fill_keys(array_keys(config('quebec-vendors.dpa_checklist')), true);
}

it('flags a service provider without a contract and outside Quebec without an assessment', function () {
    $vendor = Vendor::create(['name' => 'Stripe', 'role' => VendorRole::ServiceProvider, 'location' => 'us']);

    expect($vendor->issues())->toBe([VendorIssue::AgreementMissing, VendorIssue::AssessmentMissing]);

    $vendor->update([
        'dpa_signed_at' => now(),
        'dpa_checklist' => fullChecklist(),
        'pia_completed_at' => now(),
    ]);

    expect($vendor->issues())->toBe([]);
});

it('flags an incomplete or expired contract', function () {
    $vendor = Vendor::create([
        'name' => 'Mailer',
        'role' => VendorRole::ServiceProvider,
        'dpa_signed_at' => now(),
        'dpa_expires_at' => now()->addMonth(),
        'dpa_checklist' => ['mandate_only' => true, 'verification' => false],
    ]);

    expect($vendor->issues())->toBe([VendorIssue::AgreementIncomplete])
        ->and($vendor->missingChecklistItems())->toContain('verification', 'incident_notice')
        ->not->toContain('mandate_only')
        ->not->toContain('outside_quebec_protection'); // in Quebec

    $vendor->update(['dpa_checklist' => fullChecklist()]);
    $this->travel(2)->months();

    expect($vendor->issues())->toBe([VendorIssue::AgreementExpired]);
});

it('needs no contract from a third party', function () {
    expect(Vendor::create(['name' => 'Revenu Québec', 'role' => VendorRole::ThirdParty])->issues())->toBe([]);
});

it('asks for a review by risk tier', function () {
    $high = Vendor::create(['name' => 'Payroll', 'role' => VendorRole::ThirdParty, 'risk' => VendorRisk::High]);
    $low = Vendor::create(['name' => 'Florist', 'role' => VendorRole::ThirdParty, 'risk' => VendorRisk::Low]);

    $this->travel(13)->months();

    expect($high->issues())->toBe([VendorIssue::ReviewDue])
        ->and($low->issues())->toBe([])
        ->and($high->markReviewed()->issues())->toBe([]);
});

it('owes the destruction or return after termination', function () {
    $vendor = Vendor::create(['name' => 'Old CRM', 'role' => VendorRole::ServiceProvider])->terminate();

    expect($vendor->issues())->toBe([VendorIssue::DataReturnPending])
        ->and(Vendor::query()->scopes('active')->count())->toBe(0)
        ->and($vendor->confirmDataReturned()->issues())->toBe([]);
});

it('lists vendors and their issues', function () {
    Vendor::create(['name' => 'Stripe', 'role' => VendorRole::ServiceProvider, 'location' => 'US']);
    Vendor::create(['name' => 'Revenu Québec', 'role' => VendorRole::ThirdParty]);

    Artisan::call('quebec:vendors');
    expect(Artisan::output())->toContain('Stripe')->toContain('No written mandate or service contract')->toContain('1 vendor(s) with issues.');

    Artisan::call('quebec:vendors', ['--json' => true, '--issues' => true]);
    expect(json_decode(Artisan::output(), true))->toHaveCount(1)
        ->{0}->issues->toBe(['agreement_missing', 'assessment_missing']);
});

it('treats any other province or country as outside Quebec', function () {
    expect(Vendor::create(['name' => 'Hosting', 'role' => VendorRole::ThirdParty, 'location' => 'qc'])->isOutsideQuebec())->toBeFalse()
        ->and(Vendor::create(['name' => 'Support', 'role' => VendorRole::ThirdParty, 'location' => 'ON'])->isOutsideQuebec())->toBeTrue()
        ->and(Vendor::create(['name' => 'Analytics', 'role' => VendorRole::ThirdParty, 'location' => 'FR'])->isOutsideQuebec())->toBeTrue()
        ->and(Vendor::create(['name' => 'CDN', 'role' => VendorRole::ServiceProvider, 'location' => 'ON'])->missingChecklistItems())->toContain('outside_quebec_protection');
});
