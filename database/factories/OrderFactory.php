<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'grand_total' => fake()->randomFloat(2, 10, 1000),
            'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'cash_on_delivery']),
            'payment_status' => fake()->randomElement(['pending', 'paid', 'failed']),
            'status' => fake()->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
            'currency' => 'LKR',
            'shipping_amount' => fake()->randomFloat(2, 5, 50),
            'shipping_method' => fake()->randomElement(['standard', 'express', 'next_day']),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the order is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'paid',
        ]);
    }

    /**
     * Indicate that the order is delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'payment_status' => 'paid',
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Indicate that the payment has failed.
     */
    public function paymentFailed(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'failed',
            'status' => 'cancelled',
        ]);
    }

    /**
     * Create an order with a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create an order with cash on delivery.
     */
    public function cashOnDelivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
        ]);
    }

    /**
     * Create an order with credit card payment.
     */
    public function creditCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'credit_card',
        ]);
    }

    /**
     * Create an order with PayPal payment.
     */
    public function paypal(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'paypal',
        ]);
    }

    /**
     * Create an order with express shipping.
     */
    public function expressShipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'shipping_method' => 'express',
            'shipping_amount' => fake()->randomFloat(2, 20, 100),
        ]);
    }

    /**
     * Create an order with standard shipping.
     */
    public function standardShipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'shipping_method' => 'standard',
            'shipping_amount' => fake()->randomFloat(2, 5, 50),
        ]);
    }
}