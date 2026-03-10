<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assignableUsers = User::whereIn('role', ['manager', 'user'])->get();
        $projects = Project::all();

        Task::factory(10)->create([
            'project_id' => fn () => $projects->random()->id,
            'assigned_to' => fn () => $assignableUsers->random()->id,
        ]);
    }
}
