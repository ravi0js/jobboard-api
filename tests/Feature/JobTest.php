<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Job;
use App\Models\Category;

class JobTest extends TestCase
{
    // ── Public ───────────────────────────────────────────

    public function test_anyone_can_list_jobs(): void
    {
        Job::factory(5)->create();

        $response = $this->getJson('/api/jobs');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'total', 'per_page']);
    }

    public function test_jobs_can_be_filtered_by_location(): void
    {
        Job::factory()->create(['location' => 'Bangalore', 'status' => 'open']);
        Job::factory()->create(['location' => 'Mumbai',    'status' => 'open']);

        $response = $this->getJson('/api/jobs?location=Bangalore');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('total'));
    }

    public function test_jobs_can_be_filtered_by_type(): void
    {
        Job::factory()->create(['type' => 'remote', 'status' => 'open']);
        Job::factory()->create(['type' => 'full-time', 'status' => 'open']);

        $response = $this->getJson('/api/jobs?type=remote');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('total'));
    }

    public function test_anyone_can_view_single_job(): void
    {
        $job = Job::factory()->create();

        $response = $this->getJson("/api/jobs/{$job->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => $job->title]);
    }

    // ── Employer ─────────────────────────────────────────

    public function test_employer_can_create_job(): void
    {
        [$employer] = $this->actingAsEmployer();
        $category   = Category::factory()->create();

        $response = $this->postJson('/api/jobs', [
            'category_id' => $category->id,
            'title'       => 'Senior PHP Developer',
            'description' => 'We need a strong PHP developer.',
            'location'    => 'Bangalore',
            'type'        => 'full-time',
            'salary_min'  => 800000,
            'salary_max'  => 1200000,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Senior PHP Developer']);
    }

    public function test_candidate_cannot_create_job(): void
    {
        [$candidate] = $this->actingAsCandidate();
        $category    = Category::factory()->create();

        $response = $this->postJson('/api/jobs', [
            'category_id' => $category->id,
            'title'       => 'Unauthorized Job',
            'description' => 'Should not work.',
            'location'    => 'Bangalore',
            'type'        => 'full-time',
        ]);

        $response->assertStatus(403);
    }

    public function test_employer_can_update_own_job(): void
    {
        [$employer] = $this->actingAsEmployer();
        $job = Job::factory()->create(['user_id' => $employer->id]);

        $response = $this->putJson("/api/jobs/{$job->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Updated Title']);
    }

    public function test_employer_cannot_update_others_job(): void
    {
        [$employer] = $this->actingAsEmployer();
        $otherJob   = Job::factory()->create();

        $response = $this->putJson("/api/jobs/{$otherJob->id}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(403);
    }

    public function test_employer_can_delete_own_job(): void
    {
        [$employer] = $this->actingAsEmployer();
        $job = Job::factory()->create(['user_id' => $employer->id]);

        $response = $this->deleteJson("/api/jobs/{$job->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('jobs', ['id' => $job->id]);
    }
}