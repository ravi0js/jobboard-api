<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/jobs/{jobId}/apply",
     *     tags={"Applications"},
     *     summary="Apply for a job (Candidate only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="jobId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="cover_letter", type="string"),
     *             @OA\Property(property="resume_url", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Application submitted"),
     *     @OA\Response(response=422, description="Already applied or job closed"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function apply(Request $request, $jobId)
    {
        $job = Job::findOrFail($jobId);

        if ($job->status !== 'open') {
            return response()->json([
                'message' => 'This job is no longer accepting applications.'
            ], 422);
        }

        $alreadyApplied = JobApplication::where('job_id', $jobId)
                                        ->where('user_id', $request->user()->id)
                                        ->exists();

        if ($alreadyApplied) {
            return response()->json([
                'message' => 'You have already applied for this job.'
            ], 422);
        }

        $request->validate([
            'cover_letter' => 'nullable|string|max:2000',
            'resume_url'   => 'nullable|url',
        ]);

        $application = JobApplication::create([
            'job_id'       => $jobId,
            'user_id'      => $request->user()->id,
            'cover_letter' => $request->cover_letter,
            'resume_url'   => $request->resume_url,
            'status'       => 'pending',
        ]);

        return response()->json([
            'message'     => 'Application submitted successfully',
            'application' => $application->load(['job:id,title', 'candidate:id,name']),
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/my-applications",
     *     tags={"Applications"},
     *     summary="Get candidate's own applications",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="List of applications")
     * )
     */
    public function myApplications(Request $request)
    {
        $applications = JobApplication::where('user_id', $request->user()->id)
            ->with(['job:id,title,location,type,status', 'job.employer:id,name'])
            ->latest()
            ->paginate(10);

        return response()->json($applications);
    }

    /**
     * @OA\Delete(
     *     path="/applications/{id}",
     *     tags={"Applications"},
     *     summary="Withdraw a pending application (Candidate only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Application withdrawn"),
     *     @OA\Response(response=422, description="Cannot withdraw non-pending application")
     * )
     */
    public function withdraw(Request $request, $id)
    {
        $application = JobApplication::where('id', $id)
                                     ->where('user_id', $request->user()->id)
                                     ->firstOrFail();

        if ($application->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending applications can be withdrawn.'
            ], 422);
        }

        $application->delete();

        return response()->json([
            'message' => 'Application withdrawn successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/jobs/{jobId}/applications",
     *     tags={"Applications"},
     *     summary="Get applications for a job (Employer only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="jobId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of applications")
     * )
     */
    public function jobApplications(Request $request, $jobId)
    {
        $job = Job::findOrFail($jobId);

        if ($job->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $applications = JobApplication::where('job_id', $jobId)
            ->with(['candidate:id,name,email'])
            ->latest()
            ->paginate(10);

        return response()->json($applications);
    }

    /**
     * @OA\Get(
     *     path="/jobs/{jobId}/applications",
     *     tags={"Applications"},
     *     summary="Get applications for a job (Employer only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="jobId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of applications")
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $application = JobApplication::with('job')->findOrFail($id);

        if ($application->job->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'status' => 'required|in:reviewed,accepted,rejected',
        ]);

        $application->update(['status' => $request->status]);

        return response()->json([
            'message'     => 'Application status updated',
            'application' => $application->load(['candidate:id,name,email', 'job:id,title']),
        ]);
    }

    // Admin - All applications
    public function allApplications()
    {
        $applications = JobApplication::with([
                            'job:id,title',
                            'candidate:id,name,email'
                        ])
                        ->latest()
                        ->paginate(10);

        return response()->json($applications);
    }
}