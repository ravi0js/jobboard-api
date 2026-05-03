<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Cache::remember('categories',3600,function () {
            return Category::withCount('jobs')->get();
        });
        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string"unique:categories',
        ]);
        $category = Category::create([
            'name' => request->name,
            'slug' => \Str::slug($request->name),
        ]);
        Cache::forget('categories');

        return response()->json([
            'message' => 'Category created',
            'category'=> $category,
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        $cache::forget('categories');

        return response()->json(['message'=> 'Category Deleted']);
    }
}
