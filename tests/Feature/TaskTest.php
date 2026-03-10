<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

it('lists tasks for a project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    Task::factory()->count(3)->create(['project_id' => $project->id]);

    $response = $this->actingAs($user)->getJson("/api/v1/projects/{$project->id}/tasks");

    $response->assertOk()
        ->assertJsonStructure(['data']);
});

it('allows manager to create a task', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $assignee = User::factory()->create(['role' => 'user']);
    $project = Project::factory()->create();

    $response = $this->actingAs($manager)->postJson("/api/v1/projects/{$project->id}/tasks", [
        'title' => 'New Task',
        'description' => 'Task description',
        'status' => 'pending',
        'due_date' => now()->addMonth()->format('Y-m-d'),
        'assigned_to' => $assignee->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['title' => 'New Task']);
});

it('prevents non-manager from creating a task', function () {
    $user = User::factory()->create(['role' => 'user']);
    $project = Project::factory()->create();

    $response = $this->actingAs($user)->postJson("/api/v1/projects/{$project->id}/tasks", [
        'title' => 'New Task',
        'status' => 'pending',
        'due_date' => now()->addMonth()->format('Y-m-d'),
        'assigned_to' => $user->id,
    ]);

    $response->assertStatus(403);
});

it('shows a specific task', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create(['title' => 'Specific Task']);

    $response = $this->actingAs($user)->getJson("/api/v1/tasks/{$task->id}");

    $response->assertOk()
        ->assertJsonFragment(['title' => 'Specific Task']);
});

it('allows manager to update a task', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $task = Task::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($manager)->putJson("/api/v1/tasks/{$task->id}", [
        'status' => 'in-progress',
    ]);

    $response->assertOk()
        ->assertJsonFragment(['status' => 'in-progress']);
});

it('allows assigned user to update their task', function () {
    $user = User::factory()->create(['role' => 'user']);
    $task = Task::factory()->create([
        'assigned_to' => $user->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user)->putJson("/api/v1/tasks/{$task->id}", [
        'status' => 'in-progress',
    ]);

    $response->assertOk()
        ->assertJsonFragment(['status' => 'in-progress']);
});

it('prevents unrelated user from updating a task', function () {
    $user = User::factory()->create(['role' => 'user']);
    $otherUser = User::factory()->create(['role' => 'user']);
    $task = Task::factory()->create(['assigned_to' => $otherUser->id]);

    $response = $this->actingAs($user)->putJson("/api/v1/tasks/{$task->id}", [
        'status' => 'done',
    ]);

    $response->assertStatus(403);
});

it('allows manager to reassign a task', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $oldAssignee = User::factory()->create(['role' => 'user']);
    $newAssignee = User::factory()->create(['role' => 'user']);
    $task = Task::factory()->create(['assigned_to' => $oldAssignee->id]);

    $response = $this->actingAs($manager)->putJson("/api/v1/tasks/{$task->id}", [
        'assigned_to' => $newAssignee->id,
    ]);

    $response->assertOk()
        ->assertJsonFragment(['assigned_to' => $newAssignee->id]);
});

it('allows manager to delete a task', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $task = Task::factory()->create();

    $response = $this->actingAs($manager)->deleteJson("/api/v1/tasks/{$task->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

it('prevents non-manager from deleting a task', function () {
    $user = User::factory()->create(['role' => 'user']);
    $task = Task::factory()->create();

    $response = $this->actingAs($user)->deleteJson("/api/v1/tasks/{$task->id}");

    $response->assertStatus(403);
});

it('filters tasks by status', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    Task::factory()->create(['project_id' => $project->id, 'status' => 'pending']);
    Task::factory()->create(['project_id' => $project->id, 'status' => 'done']);

    $response = $this->actingAs($user)->getJson("/api/v1/projects/{$project->id}/tasks?status=pending");

    $response->assertOk()
        ->assertJsonFragment(['status' => 'pending'])
        ->assertJsonMissing(['status' => 'done']);
});

it('returns 401 for unauthenticated task access', function () {
    $task = Task::factory()->create();

    $response = $this->getJson("/api/v1/tasks/{$task->id}");

    $response->assertStatus(401);
});
