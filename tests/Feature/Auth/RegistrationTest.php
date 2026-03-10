<?php

use App\Models\User;

it('registers a new user with valid data', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
        'phone' => '1234567890',
        'role' => 'user',
    ]);

    $response->assertNoContent();

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'name' => 'John Doe',
        'role' => 'user',
    ]);
});

it('registers a user with manager role', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Manager Jane',
        'email' => 'jane@example.com',
        'password' => 'secret123',
        'phone' => '9876543210',
        'role' => 'manager',
    ]);

    $response->assertNoContent();

    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
        'role' => 'manager',
    ]);
});

it('registers a user with admin role', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Admin Bob',
        'email' => 'bob@example.com',
        'password' => 'secret123',
        'phone' => '5551234567',
        'role' => 'admin',
    ]);

    $response->assertNoContent();

    $this->assertDatabaseHas('users', [
        'email' => 'bob@example.com',
        'role' => 'admin',
    ]);
});

it('fails registration without required fields', function () {
    $response = $this->postJson('/api/v1/auth/register', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('fails registration with duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Another User',
        'email' => 'taken@example.com',
        'password' => 'secret123',
        'phone' => '1234567890',
        'role' => 'user',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('fails registration with short password', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => '12345',
        'phone' => '1234567890',
        'role' => 'user',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('fails registration with invalid role', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
        'phone' => '1234567890',
        'role' => 'superadmin',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

it('hashes the password on registration', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
        'phone' => '1234567890',
        'role' => 'user',
    ]);

    $user = User::where('email', 'john@example.com')->first();

    expect($user->password)->not->toBe('secret123');
    expect(password_verify('secret123', $user->password))->toBeTrue();
});
