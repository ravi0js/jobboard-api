<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthTest extends TestCase
{
    // ── Register ─────────────────────────────────────────

    public function test_candidate_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'role_id'               => 3,
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message', 'token',
                     'user' => ['id', 'name', 'email', 'role']
                 ]);
    }

    public function test_employer_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name'                  => 'Employer',
            'email'                 => 'employer@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'role_id'               => 2,
        ]);

        $response->assertStatus(201);
    }

    public function test_cannot_register_as_admin(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name'                  => 'Hacker',
            'email'                 => 'hacker@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'role_id'               => 1,
        ]);

        $response->assertStatus(422);
    }

    public function test_registration_requires_unique_email(): void
    {
        $this->createCandidate();

        $response = $this->postJson('/api/auth/register', [
            'name'                  => 'Duplicate',
            'email'                 => 'duplicate@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'role_id'               => 3,
        ]);

        // First registration
        $this->postJson('/api/auth/register', [
            'name'                  => 'Original',
            'email'                 => 'duplicate@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'role_id'               => 3,
        ]);

        $response2 = $this->postJson('/api/auth/register', [
            'name'                  => 'Duplicate',
            'email'                 => 'duplicate@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'role_id'               => 3,
        ]);

        $response2->assertStatus(422);
    }

    // ── Login ────────────────────────────────────────────

    public function test_user_can_login(): void
    {
        $this->createCandidate();

        $response = $this->postJson('/api/auth/login', [
            'email'    => \App\Models\User::first()->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'token', 'user']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $candidate = $this->createCandidate();

        $response = $this->postJson('/api/auth/login', [
            'email'    => $candidate->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }

    // ── Profile & Logout ─────────────────────────────────

    public function test_authenticated_user_can_view_profile(): void
    {
        [$candidate] = $this->actingAsCandidate();

        $response = $this->getJson('/api/profile');

        $response->assertStatus(200)
                 ->assertJsonStructure(['user' => ['id', 'name', 'email', 'role']]);
    }

    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $response = $this->getJson('/api/profile');
        $response->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        [$candidate] = $this->actingAsCandidate();

        $response = $this->postJson('/api/auth/logout');
        $response->assertStatus(200);
    }

    public function test_login_is_rate_limited_after_5_attempts(): void
    {
        $candidate = $this->createCandidate();

        // Make 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', [
                'email'    => $candidate->email,
                'password' => 'wrongpassword',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->postJson('/api/auth/login', [
            'email'    => $candidate->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429)
                ->assertJsonFragment(['message' => 'Too many login attempts. Try again in 1 minute.']);
    }

    public function test_register_is_rate_limited_after_5_attempts(): void
    {
        // Make 5 attempts
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/register', [
                'name'                  => 'Test User',
                'email'                 => "test{$i}@example.com",
                'password'              => 'password',
                'password_confirmation' => 'password',
                'role_id'               => 3,
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->postJson('/api/auth/register', [
            'name'                  => 'Test User',
            'email'                 => 'test6@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'role_id'               => 3,
        ]);

        $response->assertStatus(429);
    }
}