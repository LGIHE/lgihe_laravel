<?php

namespace Database\Factories;

use App\Models\Tender;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tender>
 */
class TenderFactory extends Factory
{
    protected $model = Tender::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(6);
        
        return [
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title),
            'reference_number' => 'TENDER/' . now()->year . '/' . fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->paragraphs(3, true),
            'requirements' => fake()->paragraphs(2, true),
            'category' => fake()->randomElement(['goods', 'services', 'works', 'consultancy']),
            'closing_date' => fake()->dateTimeBetween('now', '+60 days'),
            'status' => 'draft',
            'published_at' => null,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the tender is open.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
            'published_at' => now()->subDay(),
            'closing_date' => now()->addDays(30),
        ]);
    }

    /**
     * Indicate that the tender is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'published_at' => now()->subMonth(),
            'closing_date' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the tender is awarded.
     */
    public function awarded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'awarded',
            'published_at' => now()->subMonth(),
            'closing_date' => now()->subWeek(),
        ]);
    }

    /**
     * Indicate that the tender has an RFP document.
     */
    public function withRfpDocument(): static
    {
        return $this->state(fn (array $attributes) => [
            'rfp_document_path' => 'tender-documents/rfp/' . fake()->uuid() . '.pdf',
            'rfp_document_name' => 'rfp_' . fake()->word() . '.pdf',
            'rfp_document_type' => 'application/pdf',
            'rfp_document_size' => fake()->numberBetween(100000, 5000000),
        ]);
    }

    /**
     * Indicate that the tender has a ToR document.
     */
    public function withTorDocument(): static
    {
        return $this->state(fn (array $attributes) => [
            'tor_document_path' => 'tender-documents/tor/' . fake()->uuid() . '.pdf',
            'tor_document_name' => 'tor_' . fake()->word() . '.pdf',
            'tor_document_type' => 'application/pdf',
            'tor_document_size' => fake()->numberBetween(100000, 5000000),
        ]);
    }

    /**
     * Indicate that the tender has both RFP and ToR documents.
     */
    public function withDocuments(): static
    {
        return $this->withRfpDocument()->withTorDocument();
    }
}
