<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Donation>
 */
class DonationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(Donation::STATUSES);

        return [
            'campaign_id' => Campaign::factory(),
            'user_id' => User::factory(),
            'amount_cents' => fake()->numberBetween(1000, 100000),
            'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'bank_transfer', 'stripe']),
            'transaction_id' => fake()->unique()->uuid(),
            'gateway_transaction_id' => fake()->optional()->regexify('[A-Z]{3}[0-9]{10}'),
            'status' => $status,
            'is_anonymous' => fake()->boolean(20),
            'message' => fake()->optional(60)->sentence(),
            'completed_at' => $status === Donation::STATUS_COMPLETED ? fake()->dateTimeThisMonth() : null,
        ];
    }

    /**
     * Indicate that the donation is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Donation::STATUS_COMPLETED,
            'completed_at' => fake()->dateTimeThisMonth(),
        ]);
    }

    /**
     * Indicate that the donation is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Donation::STATUS_PENDING,
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the donation is failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Donation::STATUS_FAILED,
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the donation is refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Donation::STATUS_REFUNDED,
            'completed_at' => fake()->dateTimeThisMonth(),
        ]);
    }

    /**
     * Indicate that the donation is anonymous.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_anonymous' => true,
        ]);
    }

    /**
     * Indicate that the donation is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_anonymous' => false,
        ]);
    }
}
