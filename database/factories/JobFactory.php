<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobFactory extends Factory
{
    public function definition(): array
    {
        // Get employer role id dynamically
        $employerRoleId = Role::where('slug', 'employer')->value('id') ?? 2;

        return [
            'user_id'     => User::factory()->create(['role_id' => $employerRoleId])->id,
            'category_id' => Category::factory(),
            'title'       => fake()->jobTitle(),
            'description' => fake()->paragraphs(3, true),
            'location'    => fake()->city(),
            'type'        => fake()->randomElement(['full-time', 'part-time', 'remote', 'contract']),
            'salary_min'  => 500000,
            'salary_max'  => 1500000,
            'status'      => 'open',
            'expires_at'  => now()->addDays(30),
        ];
    }
}