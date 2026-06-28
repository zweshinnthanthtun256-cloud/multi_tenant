<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name'        => fake()->company(),
            'email'       => fake()->companyEmail(),
            'phone'       => fake()->phoneNumber(),
            'website'     => fake()->url(),
            'address'     => fake()->address(),
            'description' => fake()->paragraph(),
            
            'status'      => true,
        ];
    }
}
