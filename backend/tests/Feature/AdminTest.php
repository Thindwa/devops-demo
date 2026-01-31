<?php

use App\Models\User;

it('prevents non-admins from listing users', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);
    $this->actingAs($user);

    $this->getJson('/api/users')->assertStatus(403);
});

it('allows admins to list users', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    User::factory()->create(['role' => User::ROLE_USER]);

    $this->actingAs($admin);
    $this->getJson('/api/users')
        ->assertOk()
        ->assertJsonStructure([['id', 'name', 'email', 'role', 'created_at']]);
});

it('prevents non-admins from managing users', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);
    $this->actingAs($user);

    $this->postJson('/api/users', [
        'name' => 'X',
        'email' => 'x@example.com',
        'password' => 'password123',
        'role' => User::ROLE_USER,
    ])->assertStatus(403);

    $this->patchJson("/api/users/{$user->id}", ['name' => 'Y'])->assertStatus(403);
    $this->deleteJson("/api/users/{$user->id}")->assertStatus(403);
});

it('allows admins to create, update, and delete users (but not themselves)', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $this->actingAs($admin);

    $created = $this->postJson('/api/users', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'role' => User::ROLE_AGENT,
    ])->assertStatus(201)->json();

    $id = $created['id'];

    $this->patchJson("/api/users/{$id}", [
        'name' => 'Renamed',
        'role' => User::ROLE_ADMIN,
    ])->assertOk()->assertJsonFragment(['name' => 'Renamed'])->assertJsonFragment(['role' => User::ROLE_ADMIN]);

    $this->deleteJson("/api/users/{$id}")->assertOk()->assertJson(['ok' => true]);

    // Can't delete own account.
    $this->deleteJson("/api/users/{$admin->id}")->assertStatus(422);
});

