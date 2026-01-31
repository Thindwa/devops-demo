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

