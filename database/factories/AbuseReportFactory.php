<?php

namespace Database\Factories;

use App\Models\AbuseReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AbuseReport>
 */
class AbuseReportFactory extends Factory
{
    protected $model = AbuseReport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isAnonymous = fake()->boolean(30); // 30% chance of being anonymous

        return [
            'report_id' => AbuseReport::generateReportId(),
            'reporter_name' => $isAnonymous ? null : fake()->name(),
            'reporter_email' => $isAnonymous ? null : fake()->safeEmail(),
            'reporter_phone' => $isAnonymous ? null : fake()->phoneNumber(),
            'reporter_relationship' => $isAnonymous ? null : fake()->randomElement(['victim', 'witness', 'third-party', 'concerned-party', 'other']),
            'preferred_contact' => $isAnonymous ? null : fake()->randomElement(['email', 'phone', 'no-contact']),
            'anonymous_report' => $isAnonymous,
            'incident_type' => fake()->randomElement([
                'physical-abuse',
                'sexual-harassment',
                'sexual-assault',
                'verbal-abuse',
                'bullying',
                'discrimination',
                'stalking',
                'emotional-abuse',
                'financial-exploitation',
                'neglect',
                'other',
            ]),
            'incident_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'incident_location' => fake()->randomElement([
                'Library',
                'Classroom 101',
                'Student Dormitory',
                'Cafeteria',
                'Sports Field',
                'Parking Lot',
                'Administration Building',
                'Laboratory',
            ]),
            'persons_involved' => fake()->paragraph(),
            'detailed_description' => fake()->paragraphs(3, true),
            'witnesses_present' => fake()->boolean(50) ? fake()->paragraph() : null,
            'previously_reported' => fake()->boolean(30) ? fake()->sentence() : null,
            'evidence_available' => fake()->boolean(40) ? fake()->sentence() : null,
            'status' => fake()->randomElement(['pending', 'in_progress', 'resolved', 'closed']),
            'assigned_to' => null,
            'resolved_at' => null,
        ];
    }

    /**
     * Indicate that the report is anonymous.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'anonymous_report' => true,
            'reporter_name' => null,
            'reporter_email' => null,
            'reporter_phone' => null,
            'reporter_relationship' => null,
            'preferred_contact' => null,
        ]);
    }

    /**
     * Indicate that the report is identified (not anonymous).
     */
    public function identified(): static
    {
        return $this->state(fn (array $attributes) => [
            'anonymous_report' => false,
            'reporter_name' => fake()->name(),
            'reporter_email' => fake()->safeEmail(),
            'reporter_phone' => fake()->phoneNumber(),
            'reporter_relationship' => fake()->randomElement(['victim', 'witness', 'third-party', 'concerned-party', 'other']),
            'preferred_contact' => fake()->randomElement(['email', 'phone', 'no-contact']),
        ]);
    }

    /**
     * Indicate that the report is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'assigned_to' => null,
            'resolved_at' => null,
        ]);
    }

    /**
     * Indicate that the report is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    /**
     * Indicate that the report is resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'resolved_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}
