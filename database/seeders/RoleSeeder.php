<?php

namespace Database\Seeders;
use App\Models\Role;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('roles')->insert([
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Employer', 'slug' => 'employer'],
            ['name' => 'Candidate', 'slug' => 'candidate'],
        ]);
    }

}
