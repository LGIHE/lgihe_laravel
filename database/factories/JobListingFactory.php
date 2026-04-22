<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobListing>
 */
class JobListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->jobTitle() . ' - ' . fake()->randomElement(['Computer Science', 'Engineering', 'Business', 'Medicine']);
        
        return [
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title),
            'description' => fake()->paragraphs(3, true),
            'requirements' => fake()->paragraphs(2, true),
            'responsibilities' => fake()->paragraphs(2, true),
            'location' => fake()->city() . ', ' . fake()->country(),
            'department' => fake()->randomElement([
                'Computer Science Department',
                'Engineering Department', 
                'Business School',
                'Medical School',
                'Arts and Sciences'
            ]),
            'reports_to' => fake()->randomElement([
                'Head of Department',
                'Dean',
                'Director',
                'Vice Chancellor'
            ]),
            'supervises_who' => fake()->randomElement([
                'Teaching Assistants',
                'Research Associates',
                'Lab Assistants',
                'Administrative Staff',
                'Graduate Students'
            ]),
            'employment_type' => fake()->randomElement(['full-time', 'part-time', 'contract', 'temporary']),
            'salary_range' => 'UGX ' . number_format(fake()->numberBetween(2000000, 8000000)) . ' - ' . number_format(fake()->numberBetween(8000000, 15000000)),
            'application_deadline' => fake()->dateTimeBetween('now', '+3 months'),
            'status' => fake()->randomElement(['draft', 'active', 'closed']),
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the job listing is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the job listing is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the job listing has a document.
     */
    public function withDocument(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_path' => 'job-documents/sample-job-description.pdf',
            'document_name' => 'Job Description.pdf',
            'document_type' => 'application/pdf',
            'document_size' => fake()->numberBetween(100000, 2000000),
        ]);
    }
}
