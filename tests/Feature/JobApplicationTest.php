<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Job;
use App\Models\JobApplication;

class JobApplicationTest extends TestCase
{
    public function test_candidate_can_apply_for_job(): void
    {
        [$candidate] = $this->actingAsCandidate();
        $job = Job::factory()->create(['status' => 'open']);

        $response = $this->postJson("/api/jobs/{$job->id}/apply", [
            'cover_letter' => 'I am a great PHP developer.',
            'resume_url'   => 'https://ravi-kr.netlify.app',
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['status' => 'pending']);
    }

    public function test_candidate_cannot_apply_twice(): void
    {
        [$candidate] = $this->actingAsCandidate();
        $job = Job::factory()->create(['status' => 'open']);

        $this->postJson("/api/jobs/{$job->id}/apply", [
            'cover_letter' => 'First application.',
        ]);

        $response = $this->postJson("/api/jobs/{$job->id}/apply", [
            'cover_letter' => 'Second application.',
        ]);

        $response->assertStatus(422)
                 ->assertJsonFragment(['message' => 'You have already applied for this job.']);
    }

    public function test_candidate_cannot_apply_to_closed_job(): void
    {
        [$candidate] = $this->actingAsCandidate();
        $job = Job::factory()->create(['status' => 'closed']);

        $response = $this->postJson("/api/jobs/{$job->id}/apply");

        $response->assertStatus(422)
                 ->assertJsonFragment(['message' => 'This job is no longer accepting applications.']);
    }

    public function test_employer_cannot_apply_for_job(): void
    {
        [$employer] = $this->actingAsEmployer();
        $job = Job::factory()->create(['status' => 'open']);

        $response = $this->postJson("/api/jobs/{$job->id}/apply");
        $response->assertStatus(403);
    }

    public function test_candidate_can_view_own_applications(): void
    {
        [$candidate] = $this->actingAsCandidate();
        $job = Job::factory()->create();

        JobApplication::create([
            'job_id'  => $job->id,
            'user_id' => $candidate->id,
            'status'  => 'pending',
        ]);

        $response = $this->getJson('/api/my-applications');
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    public function test_employer_can_update_application_status(): void
    {
        [$employer] = $this->actingAsEmployer();
        $job = Job::factory()->create(['user_id' => $employer->id]);

        $candidate    = $this->createCandidate();
        $application  = JobApplication::create([
            'job_id'  => $job->id,
            'user_id' => $candidate->id,
            'status'  => 'pending',
        ]);

        $response = $this->putJson("/api/applications/{$application->id}/status", [
            'status' => 'accepted',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'accepted']);
    }

    public function test_candidate_can_withdraw_pending_application(): void
    {
        [$candidate] = $this->actingAsCandidate();
        $job = Job::factory()->create();

        $application = JobApplication::create([
            'job_id'  => $job->id,
            'user_id' => $candidate->id,
            'status'  => 'pending',
        ]);

        $response = $this->deleteJson("/api/applications/{$application->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('job_applications', ['id' => $application->id]);
    }
}