<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('???-###')),
            'price' => fake()->randomFloat(2, 5, 500),
            'stock_quantity' => fake()->numberBetween(0, 200),
        ];
    }
}