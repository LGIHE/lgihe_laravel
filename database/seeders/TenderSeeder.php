<?php

namespace Database\Seeders;

use App\Models\Tender;
use App\Models\User;
use Illuminate\Database\Seeder;

class TenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();

        if (!$admin) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $tenders = [
            [
                'title' => 'Supply of Laboratory Equipment',
                'slug' => 'supply-of-laboratory-equipment',
                'reference_number' => 'TENDER/2026/001',
                'description' => '<p>The institution invites sealed bids from eligible and qualified suppliers for the supply, delivery, installation, testing, and commissioning of laboratory equipment.</p>',
                'requirements' => '<ul><li>Must be a registered company</li><li>Minimum 5 years experience in laboratory equipment supply</li><li>Valid tax clearance certificate</li><li>Financial capability to execute the contract</li></ul>',
                'category' => 'goods',
                'closing_date' => now()->addDays(30),
                'status' => 'open',
                'published_at' => now(),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ],
            [
                'title' => 'Construction of New Library Building',
                'slug' => 'construction-of-new-library-building',
                'reference_number' => 'TENDER/2026/002',
                'description' => '<p>The institution seeks qualified contractors for the construction of a modern library building with a capacity of 500 students.</p>',
                'requirements' => '<ul><li>Valid contractor registration certificate</li><li>Completed at least 3 similar projects</li><li>Financial statements for the last 3 years</li><li>Valid insurance cover</li></ul>',
                'category' => 'works',
                'closing_date' => now()->addDays(45),
                'status' => 'open',
                'published_at' => now(),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ],
            [
                'title' => 'Consultancy Services for Strategic Plan Development',
                'slug' => 'consultancy-services-for-strategic-plan-development',
                'reference_number' => 'TENDER/2026/003',
                'description' => '<p>The institution invites proposals from qualified consulting firms to develop a comprehensive 5-year strategic plan.</p>',
                'requirements' => '<ul><li>Registered consulting firm</li><li>Team leader with PhD and 10+ years experience</li><li>Experience in higher education strategic planning</li><li>Demonstrated track record</li></ul>',
                'category' => 'consultancy',
                'closing_date' => now()->addDays(21),
                'status' => 'open',
                'published_at' => now(),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ],
            [
                'title' => 'Provision of Cleaning Services',
                'slug' => 'provision-of-cleaning-services',
                'reference_number' => 'TENDER/2026/004',
                'description' => '<p>The institution requires professional cleaning services for all campus facilities for a period of 12 months.</p>',
                'requirements' => '<ul><li>Registered service provider</li><li>Minimum 3 years experience</li><li>Adequate staff and equipment</li><li>Valid business license</li></ul>',
                'category' => 'services',
                'closing_date' => now()->addDays(14),
                'status' => 'open',
                'published_at' => now(),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ],
            [
                'title' => 'Supply of ICT Equipment',
                'slug' => 'supply-of-ict-equipment',
                'reference_number' => 'TENDER/2026/005',
                'description' => '<p>Sealed bids are invited for the supply of computers, servers, and networking equipment.</p>',
                'requirements' => '<ul><li>Authorized dealer/distributor</li><li>Technical support capability</li><li>Warranty and after-sales service</li><li>Competitive pricing</li></ul>',
                'category' => 'goods',
                'closing_date' => now()->addDays(25),
                'status' => 'open',
                'published_at' => now(),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ],
        ];

        foreach ($tenders as $tenderData) {
            Tender::create($tenderData);
        }

        $this->command->info('Sample tenders created successfully!');
    }
}
