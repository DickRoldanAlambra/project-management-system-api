<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

it('lists comments for a task', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create();
    Comment::factory()->count(3)->create(['task_id' => $task->id]);

    $response = $this->actingAs($user)->getJson("/api/v1/tasks/{$task->id}/comments");

    $response->assertOk()
        ->assertJsonStructure(['data']);
});

it('allows authenticated user to add a comment', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create();

    $response = $this->actingAs($user)->postJson("/api/v1/tasks/{$task->id}/comments", [
        'body' => 'This is a test comment.',
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['body' => 'This is a test comment.']);

    $this->assertDatabaseHas('comments', [
        'body' => 'This is a test comment.',
        'task_id' => $task->id,
        'user_id' => $user->id,
    ]);
});

it('associates comment with the authenticated user', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create();

    $this->actingAs($user)->postJson("/api/v1/tasks/{$task->id}/comments", [
        'body' => 'My comment',
    ]);

    $comment = Comment::where('body', 'My comment')->first();

    expect($comment->user_id)->toBe($user->id);
    expect($comment->task_id)->toBe($task->id);
});

it('fails to add a comment without body', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create();

    $response = $this->actingAs($user)->postJson("/api/v1/tasks/{$task->id}/comments", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['body']);
});

it('returns 401 for unauthenticated comment creation', function () {
    $task = Task::factory()->create();

    $response = $this->postJson("/api/v1/tasks/{$task->id}/comments", [
        'body' => 'Test comment',
    ]);

    $response->assertStatus(401);
});

it('returns comments with user data eager loaded', function () {
    $user = User::factory()->create(['name' => 'Commenter']);
    $task = Task::factory()->create();
    Comment::factory()->create([
        'task_id' => $task->id,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->getJson("/api/v1/tasks/{$task->id}/comments");

    $response->assertOk()
        ->assertJsonFragment(['name' => 'Commenter']);
});
