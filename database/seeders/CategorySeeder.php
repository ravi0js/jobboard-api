<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $categories = [
        ['name' => 'Backend Development',   'slug' => 'backend-development'],
        ['name' => 'Frontend Development',  'slug' => 'frontend-development'],
        ['name' => 'DevOps',                'slug' => 'devops'],
        ['name' => 'Mobile Development',    'slug' => 'mobile-development'],
        ['name' => 'Data Science',          'slug' => 'data-science'],
    ];

    foreach ($categories as $category) {
        \App\Models\Category::create($category);
    }
}
}
