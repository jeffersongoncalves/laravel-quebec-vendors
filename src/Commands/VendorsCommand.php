<?php

namespace JeffersonGoncalves\QuebecVendors\Commands;

use Illuminate\Console\Command;
use JeffersonGoncalves\QuebecVendors\Enums\VendorIssue;
use JeffersonGoncalves\QuebecVendors\Models\Vendor;

class VendorsCommand extends Command
{
    protected $signature = 'quebec:vendors {--json : Output as JSON} {--issues : Only vendors with issues}';

    protected $description = 'List the vendors you communicate personal information to and what each one still owes';

    public function handle(): int
    {
        $rows = Vendor::query()->orderBy('name')->get()
            ->map(fn (Vendor $vendor): array => [
                'id' => $vendor->getKey(),
                'name' => $vendor->name,
                'role' => $vendor->role->value,
                'location' => $vendor->location,
                'risk' => $vendor->risk->value,
                'next_review' => $vendor->terminated_at === null ? $vendor->nextReviewAt()->toDateString() : null,
                'issues' => array_map(fn (VendorIssue $issue): string => $issue->value, $vendor->issues()),
            ]);

        $withIssues = $rows->filter(fn (array $row): bool => $row['issues'] !== [])->values();

        if ($this->option('issues')) {
            $rows = $withIssues;
        }

        if ($this->option('json')) {
            $this->line((string) json_encode($rows->values()->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        if ($rows->isEmpty()) {
            $this->info('No vendors to show.');

            return self::SUCCESS;
        }

        $this->table(
            ['#', 'Name', 'Role', 'Location', 'Risk', 'Next review', 'Issues'],
            $rows->map(fn (array $row): array => [
                ...array_slice($row, 0, 6),
                $this->describe($row['issues']),
            ])->all(),
        );

        $withIssues = $withIssues->count();

        if ($withIssues > 0) {
            $this->warn("{$withIssues} vendor(s) with issues.");
        }

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $issues
     */
    protected function describe(array $issues): string
    {
        return $issues === [] ? '-' : implode("\n", array_map(fn (string $issue): string => VendorIssue::from($issue)->description(), $issues));
    }
}
