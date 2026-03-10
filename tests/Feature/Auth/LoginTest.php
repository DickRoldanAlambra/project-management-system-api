<?php

use App\Models\User;

it('logs in with valid credentials', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => bcrypt('secret123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token']);
});

it('fails login with wrong password', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => bcrypt('secret123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'john@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Invalid credentials']);
});

it('fails login with non-existent email', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'secret123',
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Invalid credentials']);
});

it('fails login without required fields', function () {
    $response = $this->postJson('/api/v1/auth/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

it('returns a valid sanctum token on login', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => bcrypt('secret123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);

    $token = $response->json('token');

    $this->getJson('/api/v1/me', [
        'Authorization' => "Bearer {$token}",
    ])->assertOk()
        ->assertJsonFragment(['email' => 'john@example.com']);
});

it('logs out and invalidates token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api')->plainTextToken;

    expect($user->tokens()->count())->toBe(1);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/logout');

    $response->assertNoContent();

    expect($user->fresh()->tokens()->count())->toBe(0);
});

it('returns authenticated user data on /me', function () {
    $user = User::factory()->create([
        'name' => 'Jane Doe',
        'role' => 'manager',
    ]);

    $response = $this->actingAs($user)->getJson('/api/v1/me');

    $response->assertOk()
        ->assertJsonFragment([
            'name' => 'Jane Doe',
            'role' => 'manager',
        ]);
});

it('returns 401 for unauthenticated /me request', function () {
    $response = $this->getJson('/api/v1/me');

    $response->assertStatus(401);
});
