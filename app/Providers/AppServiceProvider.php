<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Global API rate limit - 60 requests per minute per IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                        ->by($request->user()?->id ?: $request->ip())
                        ->response(function () {
                            return response()->json([
                                'message' => 'Too many requests. Please slow down.',
                                'retry_after' => '60 seconds',
                            ], 429);
                        });
        });

        // Strict limit for auth endpoints - prevent brute force
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)
                        ->by($request->ip())
                        ->response(function () {
                            return response()->json([
                                'message' => 'Too many login attempts. Try again in 1 minute.',
                                'retry_after' => '60 seconds',
                            ], 429);
                        });
        });

        // Job creation limit - prevent spam
        RateLimiter::for('jobs', function (Request $request) {
            return Limit::perMinute(10)
                        ->by($request->user()?->id ?: $request->ip())
                        ->response(function () {
                            return response()->json([
                                'message' => 'Too many job posts. Limit is 10 per minute.',
                            ], 429);
                        });
        });

        // Application limit - prevent spam applying
        RateLimiter::for('applications', function (Request $request) {
            return Limit::perMinute(10)
                        ->by($request->user()?->id ?: $request->ip())
                        ->response(function () {
                            return response()->json([
                                'message' => 'Too many applications. Limit is 10 per minute.',
                            ], 429);
                        });
        });
    }
}