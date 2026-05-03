<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    // Admin
    \App\Models\User::create([
        'name'     => 'Admin User',
        'email'    => 'admin@jobboard.com',
        'password' => bcrypt('password'),
        'role_id'  => 1,
    ]);

    // Employer
    \App\Models\User::create([
        'name'     => 'Employer User',
        'email'    => 'employer@jobboard.com',
        'password' => bcrypt('password'),
        'role_id'  => 2,
    ]);

    // Candidate
    \App\Models\User::create([
        'name'     => 'Candidate User',
        'email'    => 'candidate@jobboard.com',
        'password' => bcrypt('password'),
        'role_id'  => 3,
    ]);
}
}
