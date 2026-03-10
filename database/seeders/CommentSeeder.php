<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $tasks = Task::all();

        Comment::factory(10)->create([
            'task_id' => fn () => $tasks->random()->id,
            'user_id' => fn () => $users->random()->id,
        ]);
    }
}
