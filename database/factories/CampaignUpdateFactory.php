<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CampaignUpdate>
 */
class CampaignUpdateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'author_id' => User::factory(),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraphs(2, true),
            'update_type' => fake()->randomElement(['general', 'milestone', 'thank_you', 'urgent', 'progress']),
            'is_pinned' => fake()->boolean(10),
        ];
    }

    /**
     * Indicate that the update is pinned.
     */
    public function pinned(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pinned' => true,
        ]);
    }

    /**
     * Indicate that the update is not pinned.
     */
    public function unpinned(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pinned' => false,
        ]);
    }

    /**
     * Set the update type to milestone.
     */
    public function milestone(): static
    {
        return $this->state(fn (array $attributes) => [
            'update_type' => 'milestone',
            'title' => 'Milestone Reached: '.fake()->sentence(3),
        ]);
    }

    /**
     * Set the update type to urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'update_type' => 'urgent',
            'title' => 'Urgent Update: '.fake()->sentence(3),
            'is_pinned' => true,
        ]);
    }
}
