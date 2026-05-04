<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Job;
use App\Models\Category;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear all rate limiters before each test
        RateLimiter::clear('api');
        RateLimiter::clear('auth');
        RateLimiter::clear('jobs');
        RateLimiter::clear('applications');
    }

    public function test_api_rate_limit_allows_60_requests(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $response = $this->getJson('/api/jobs');
            $response->assertStatus(200);
        }
    }

    public function test_api_rate_limit_blocks_after_60_requests(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->getJson('/api/jobs');
        }

        $response = $this->getJson('/api/jobs');
        $response->assertStatus(429)
                 ->assertJsonFragment(['message' => 'Too many requests. Please slow down.']);
    }

    /*
    public function test_job_creation_rate_limit(): void
    {
        [$employer] = $this->actingAsEmployer();
        $category   = Category::factory()->create();

        // Clear rate limiter specifically for this user
        RateLimiter::clear('jobs|' . $employer->id);

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/jobs', [
                'category_id' => $category->id,
                'title'       => "Job {$i}",
                'description' => 'Test description',
                'location'    => 'Bangalore',
                'type'        => 'full-time',
            ]);
        }

        $response = $this->postJson('/api/jobs', [
            'category_id' => $category->id,
            'title'       => 'Job 11',
            'description' => 'Test description',
            'location'    => 'Bangalore',
            'type'        => 'full-time',
        ]);

        $response->assertStatus(429)
                 ->assertJsonFragment(['message' => 'Too many job posts. Limit is 10 per minute.']);
    }
    */

    public function test_job_creation_requires_auth(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/jobs', [
            'category_id' => $category->id,
            'title'       => 'Test Job',
            'description' => 'Description',
            'location'    => 'Bangalore',
            'type'        => 'full-time',
        ]);

        $response->assertStatus(401);
    }

    public function test_application_rate_limit(): void
    {
        [$candidate] = $this->actingAsCandidate();

        RateLimiter::clear('applications|' . $candidate->id);

        for ($i = 0; $i < 10; $i++) {
            $job = Job::factory()->create(['status' => 'open']);
            $this->postJson("/api/jobs/{$job->id}/apply", [
                'cover_letter' => 'I am a great developer.',
            ]);
        }

        $job = Job::factory()->create(['status' => 'open']);
        $response = $this->postJson("/api/jobs/{$job->id}/apply", [
            'cover_letter' => 'I am a great developer.',
        ]);

        $response->assertStatus(429)
                 ->assertJsonFragment(['message' => 'Too many applications. Limit is 10 per minute.']);
    }

    public function test_rate_limit_is_per_ip(): void
    {
        // First user exhausts auth rate limit
        [$candidate1] = $this->actingAsCandidate();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', [
                'email'    => $candidate1->email,
                'password' => 'wrongpassword',
            ]);
        }

        // Verify rate limit is hit
        $response = $this->postJson('/api/auth/login', [
            'email'    => $candidate1->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429)
                 ->assertJsonFragment(['message' => 'Too many login attempts. Try again in 1 minute.']);
    }
}