<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class JobController extends Controller
{
    /**
     * @OA\Get(
     *     path="/jobs",
     *     tags={"Jobs"},
     *     summary="List all open jobs",
     *     @OA\Parameter(name="location", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string", enum={"full-time","part-time","remote","contract"})),
     *     @OA\Parameter(name="category_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Paginated job listings")
     * )
     */
    public function index(Request $request)
    {
        $cacheKey = 'jobs_' . md5(json_encode($request->all()));

        $jobs = Cache::remember($cacheKey, 300, function () use ($request) {
            $query = Job::with(['employer:id,name', 'category:id,name'])
                        ->where('status', 'open');

            if ($request->category_id) {
                $query->where('category_id', $request->category_id);
            }
            if ($request->location) {
                $query->where('location', 'like', '%' . $request->location . '%');
            }
            if ($request->type) {
                $query->where('type', $request->type);
            }
            if ($request->search) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            return $query->latest()->paginate(10);
        });

        return response()->json($jobs);
    }

    /**
     * @OA\Get(
     *     path="/jobs",
     *     tags={"Jobs"},
     *     summary="List all open jobs",
     *     @OA\Parameter(name="location", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string", enum={"full-time","part-time","remote","contract"})),
     *     @OA\Parameter(name="category_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Paginated job listings")
     * )
     */
    public function show($id)
    {
        $job = Cache::remember("job_{$id}", 300, function () use ($id) {
            return Job::with(['employer:id,name', 'category:id,name'])
                      ->findOrFail($id);
        });

        return response()->json($job);
    }

    /**
     * @OA\Post(
     *     path="/jobs",
     *     tags={"Jobs"},
     *     summary="Create a job (Employer only)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_id","title","description","location","type"},
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Senior PHP Developer"),
     *             @OA\Property(property="description", type="string", example="We need a PHP developer"),
     *             @OA\Property(property="location", type="string", example="Bangalore"),
     *             @OA\Property(property="type", type="string", example="full-time"),
     *             @OA\Property(property="salary_min", type="number", example=800000),
     *             @OA\Property(property="salary_max", type="number", example=1200000)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Job created"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'title'        => 'required|string|max:255',
            'description'  => 'required|string',
            'location'     => 'required|string',
            'type'         => 'required|in:full-time,part-time,remote,contract',
            'salary_min'   => 'nullable|numeric',
            'salary_max'   => 'nullable|numeric',
            'expires_at'   => 'nullable|date|after:today',
        ]);

        $job = $request->user()->jobs()->create($request->all());

        Cache::flush();

        return response()->json([
            'message' => 'Job created successfully',
            'job'     => $job->load(['employer:id,name', 'category:id,name']),
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/jobs/{id}",
     *     tags={"Jobs"},
     *     summary="Update a job (Employer only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Job updated"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function update(Request $request, $id)
    {
        $job = Job::findOrFail($id);

        if ($job->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'location'    => 'sometimes|string',
            'type'        => 'sometimes|in:full-time,part-time,remote,contract',
            'status'      => 'sometimes|in:open,closed',
            'salary_min'  => 'nullable|numeric',
            'salary_max'  => 'nullable|numeric',
            'expires_at'  => 'nullable|date',
        ]);

        $job->update($request->all());

        Cache::flush();

        return response()->json([
            'message' => 'Job updated successfully',
            'job'     => $job->load(['employer:id,name', 'category:id,name']),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/jobs/{id}",
     *     tags={"Jobs"},
     *     summary="Delete a job (Employer only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Job deleted"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function destroy(Request $request, $id)
    {
        $job = Job::findOrFail($id);

        if ($job->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $job->delete();
        Cache::flush();

        return response()->json(['message' => 'Job deleted successfully']);
    }

    // Employer - My jobs
    public function myJobs(Request $request)
    {
        $jobs = $request->user()
                        ->jobs()
                        ->with('category:id,name')
                        ->latest()
                        ->paginate(10);

        return response()->json($jobs);
    }
}