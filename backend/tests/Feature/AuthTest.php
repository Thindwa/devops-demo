<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('logs in and returns a token', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
        'role' => User::ROLE_USER,
    ]);

    $res = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $res->assertOk()
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong',
    ])->assertStatus(401);
});

