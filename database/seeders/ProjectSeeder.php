<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = User::where('role', 'admin')->get();

        Project::factory(5)->create([
            'created_by' => fn () => $admins->random()->id,
        ]);
    }
}
