<?php

use App\Models\Project;
use App\Models\User;

it('lists projects for authenticated user', function () {
    $user = User::factory()->create();
    Project::factory()->count(3)->create();

    $response = $this->actingAs($user)->getJson('/api/v1/projects');

    $response->assertOk()
        ->assertJsonStructure(['data']);
});

it('returns 401 for unauthenticated project list', function () {
    $response = $this->getJson('/api/v1/projects');

    $response->assertStatus(401);
});

it('allows admin to create a project', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->postJson('/api/v1/projects', [
        'title' => 'New Project',
        'description' => 'A test project',
        'start_date' => '2026-04-01',
        'end_date' => '2026-06-01',
        'created_by' => $admin->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['title' => 'New Project']);

    $this->assertDatabaseHas('projects', ['title' => 'New Project']);
});

it('prevents non-admin from creating a project', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user)->postJson('/api/v1/projects', [
        'title' => 'New Project',
        'description' => 'A test project',
        'start_date' => '2026-04-01',
        'end_date' => '2026-06-01',
        'created_by' => $user->id,
    ]);

    $response->assertStatus(403);
});

it('prevents manager from creating a project', function () {
    $manager = User::factory()->create(['role' => 'manager']);

    $response = $this->actingAs($manager)->postJson('/api/v1/projects', [
        'title' => 'New Project',
        'description' => 'A test project',
        'start_date' => '2026-04-01',
        'end_date' => '2026-06-01',
        'created_by' => $manager->id,
    ]);

    $response->assertStatus(403);
});

it('validates required fields when creating a project', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->postJson('/api/v1/projects', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'start_date', 'end_date']);
});

it('validates end_date is after or equal to start_date', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->postJson('/api/v1/projects', [
        'title' => 'Bad Dates Project',
        'start_date' => '2026-06-01',
        'end_date' => '2026-04-01',
        'created_by' => $admin->id,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['end_date']);
});

it('shows a specific project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['title' => 'Visible Project']);

    $response = $this->actingAs($user)->getJson("/api/v1/projects/{$project->id}");

    $response->assertOk()
        ->assertJsonFragment(['title' => 'Visible Project']);
});

it('allows admin to update a project', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $project = Project::factory()->create(['created_by' => $admin->id]);

    $response = $this->actingAs($admin)->putJson("/api/v1/projects/{$project->id}", [
        'title' => 'Updated Title',
    ]);

    $response->assertOk()
        ->assertJsonFragment(['title' => 'Updated Title']);
});

it('allows admin to delete a project', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $project = Project::factory()->create(['created_by' => $admin->id]);

    $response = $this->actingAs($admin)->deleteJson("/api/v1/projects/{$project->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
});

it('prevents non-admin from deleting a project', function () {
    $user = User::factory()->create(['role' => 'user']);
    $project = Project::factory()->create();

    $response = $this->actingAs($user)->deleteJson("/api/v1/projects/{$project->id}");

    $response->assertStatus(403);
});

it('filters projects by title', function () {
    $user = User::factory()->create();
    Project::factory()->create(['title' => 'Alpha Project']);
    Project::factory()->create(['title' => 'Beta Project']);

    $response = $this->actingAs($user)->getJson('/api/v1/projects?title=Alpha');

    $response->assertOk()
        ->assertJsonFragment(['title' => 'Alpha Project'])
        ->assertJsonMissing(['title' => 'Beta Project']);
});
