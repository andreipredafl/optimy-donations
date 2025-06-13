<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);
        $startDate = fake()->dateTimeBetween('-1 month', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+6 months');
        $goalAmountCents = fake()->numberBetween(100000, 5000000);

        return [
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title),
            'description' => fake()->paragraphs(3, true),
            'goal_amount_cents' => $goalAmountCents,
            'current_amount_cents' => fake()->numberBetween(0, intval($goalAmountCents * 0.8)),
            'creator_id' => User::factory(),
            'category_id' => Category::factory(),
            'status' => fake()->randomElement(Campaign::STATUSES),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'featured_image_url' => fake()->optional()->imageUrl(800, 600, 'business'),
            'donations_count' => fake()->numberBetween(0, 100),
            'donors_count' => fake()->numberBetween(0, 80),
        ];
    }

    /**
     * Indicate that the campaign is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Campaign::STATUS_ACTIVE,
            'start_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'end_date' => fake()->dateTimeBetween('now', '+6 months'),
        ]);
    }

    /**
     * Indicate that the campaign is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Campaign::STATUS_COMPLETED,
                'current_amount_cents' => $attributes['goal_amount_cents'],
                'start_date' => fake()->dateTimeBetween('-6 months', '-1 month'),
                'end_date' => fake()->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    /**
     * Indicate that the campaign is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Campaign::STATUS_CANCELLED,
        ]);
    }

    /**
     * Indicate that the campaign is ongoing.
     */
    public function ongoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Campaign::STATUS_ACTIVE,
            'start_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'end_date' => fake()->dateTimeBetween('+1 week', '+6 months'),
        ]);
    }
}
