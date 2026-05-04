<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected bool $seed = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();

        // Clear rate limiter between tests
        \Illuminate\Support\Facades\RateLimiter::clear('api');
        \Illuminate\Support\Facades\RateLimiter::clear('auth');
        \Illuminate\Support\Facades\RateLimiter::clear('jobs');
        \Illuminate\Support\Facades\RateLimiter::clear('applications');
    }

    protected function seedRoles(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('roles')->truncate();
        \DB::table('roles')->insert([
        ['id' => 1, 'name' => 'Admin',     'slug' => 'admin',     'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'name' => 'Employer',  'slug' => 'employer',  'created_at' => now(), 'updated_at' => now()],
        ['id' => 3, 'name' => 'Candidate', 'slug' => 'candidate', 'created_at' => now(), 'updated_at' => now()],
    ]);
    \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    protected function createAdmin(): User
    {
        return User::factory()->create(['role_id' => 1]);
    }

    protected function createEmployer(): User
    {
        return User::factory()->create(['role_id' => 2]);
    }

    protected function createCandidate(): User
    {
        return User::factory()->create(['role_id' => 3]);
    }

    protected function actingAsEmployer(): array
    {
        $employer = $this->createEmployer();
        $this->actingAs($employer, 'sanctum');
        return [$employer];
    }

    protected function actingAsCandidate(): array
    {
        $candidate = $this->createCandidate();
        $this->actingAs($candidate, 'sanctum');
        return [$candidate];
    }

    protected function actingAsAdmin(): array
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin, 'sanctum');
        return [$admin];
    }
}