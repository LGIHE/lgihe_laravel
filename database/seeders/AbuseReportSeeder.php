<?php

namespace Database\Seeders;

use App\Models\AbuseReport;
use Illuminate\Database\Seeder;

class AbuseReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 5 pending reports
        AbuseReport::factory()
            ->count(5)
            ->pending()
            ->create();

        // Create 3 in-progress reports
        AbuseReport::factory()
            ->count(3)
            ->inProgress()
            ->create();

        // Create 2 resolved reports
        AbuseReport::factory()
            ->count(2)
            ->resolved()
            ->create();

        // Create 3 anonymous reports
        AbuseReport::factory()
            ->count(3)
            ->anonymous()
            ->pending()
            ->create();

        // Create 2 identified reports
        AbuseReport::factory()
            ->count(2)
            ->identified()
            ->pending()
            ->create();

        $this->command->info('Created 15 abuse reports for testing.');
    }
}
