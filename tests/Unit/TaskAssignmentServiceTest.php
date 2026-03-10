<?php

use App\Jobs\SendTaskAssignmentNotification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
    $this->service = new TaskAssignmentService;
});

it('assigns a task to a valid user', function () {
    $user = User::factory()->create(['role' => 'user']);
    $project = Project::factory()->create();

    $task = $this->service->assign([
        'title' => 'Test Task',
        'description' => 'Test description',
        'status' => 'pending',
        'due_date' => now()->addWeek()->format('Y-m-d'),
        'project_id' => $project->id,
        'assigned_to' => $user->id,
    ]);

    expect($task)->toBeInstanceOf(Task::class);
    expect($task->assigned_to)->toBe($user->id);
    expect($task->title)->toBe('Test Task');
});

it('dispatches notification job on assignment', function () {
    $user = User::factory()->create(['role' => 'user']);
    $project = Project::factory()->create();

    $this->service->assign([
        'title' => 'Notify Task',
        'description' => 'Notify description',
        'status' => 'pending',
        'due_date' => now()->addWeek()->format('Y-m-d'),
        'project_id' => $project->id,
        'assigned_to' => $user->id,
    ]);

    Queue::assertPushed(SendTaskAssignmentNotification::class);
});

it('prevents assigning tasks to admin users', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $project = Project::factory()->create();

    $this->service->assign([
        'title' => 'Admin Task',
        'status' => 'pending',
        'due_date' => now()->addWeek()->format('Y-m-d'),
        'project_id' => $project->id,
        'assigned_to' => $admin->id,
    ]);
})->throws(ValidationException::class);

it('prevents assigning more than 10 active tasks to a user', function () {
    $user = User::factory()->create(['role' => 'user']);
    $project = Project::factory()->create();

    Task::factory()->count(10)->create([
        'assigned_to' => $user->id,
        'status' => 'pending',
    ]);

    $this->service->assign([
        'title' => 'Eleventh Task',
        'status' => 'pending',
        'due_date' => now()->addWeek()->format('Y-m-d'),
        'project_id' => $project->id,
        'assigned_to' => $user->id,
    ]);
})->throws(ValidationException::class);

it('allows assignment when user has 10 tasks but some are done', function () {
    $user = User::factory()->create(['role' => 'user']);
    $project = Project::factory()->create();

    Task::factory()->count(8)->create([
        'assigned_to' => $user->id,
        'status' => 'pending',
    ]);
    Task::factory()->count(5)->create([
        'assigned_to' => $user->id,
        'status' => 'done',
    ]);

    $task = $this->service->assign([
        'title' => 'Another Task',
        'description' => 'Another description',
        'status' => 'pending',
        'due_date' => now()->addWeek()->format('Y-m-d'),
        'project_id' => $project->id,
        'assigned_to' => $user->id,
    ]);

    expect($task->title)->toBe('Another Task');
});

it('prevents assigning a task with a past due date', function () {
    $user = User::factory()->create(['role' => 'user']);
    $project = Project::factory()->create();

    $this->service->assign([
        'title' => 'Past Task',
        'status' => 'pending',
        'due_date' => now()->subWeek()->format('Y-m-d'),
        'project_id' => $project->id,
        'assigned_to' => $user->id,
    ]);
})->throws(ValidationException::class);

it('reassigns a task to a new user', function () {
    $oldUser = User::factory()->create(['role' => 'user']);
    $newUser = User::factory()->create(['role' => 'user']);
    $task = Task::factory()->create([
        'assigned_to' => $oldUser->id,
        'due_date' => now()->addWeek(),
    ]);

    $updatedTask = $this->service->reassign($task, $newUser->id);

    expect($updatedTask->assigned_to)->toBe($newUser->id);
});

it('dispatches notification on reassignment', function () {
    $oldUser = User::factory()->create(['role' => 'user']);
    $newUser = User::factory()->create(['role' => 'user']);
    $task = Task::factory()->create([
        'assigned_to' => $oldUser->id,
        'due_date' => now()->addWeek(),
    ]);

    $this->service->reassign($task, $newUser->id);

    Queue::assertPushed(SendTaskAssignmentNotification::class);
});

it('does not dispatch notification when reassigning to the same user', function () {
    $user = User::factory()->create(['role' => 'user']);
    $task = Task::factory()->create([
        'assigned_to' => $user->id,
        'due_date' => now()->addWeek(),
    ]);

    $this->service->reassign($task, $user->id);

    Queue::assertNotPushed(SendTaskAssignmentNotification::class);
});

it('prevents reassigning a task to an admin', function () {
    $user = User::factory()->create(['role' => 'user']);
    $admin = User::factory()->create(['role' => 'admin']);
    $task = Task::factory()->create([
        'assigned_to' => $user->id,
        'due_date' => now()->addWeek(),
    ]);

    $this->service->reassign($task, $admin->id);
})->throws(ValidationException::class);
