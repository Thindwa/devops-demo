<?php

use App\Models\Ticket;
use App\Models\User;

it('requires auth to list tickets', function () {
    $this->getJson('/api/tickets')->assertStatus(401);
});

it('allows a user to create a ticket', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);
    $this->actingAs($user);

    $res = $this->postJson('/api/tickets', [
        'title' => 'Login issue',
        'description' => 'Cannot login',
    ]);

    $res->assertStatus(201)
        ->assertJsonFragment(['title' => 'Login issue'])
        ->assertJsonFragment(['status' => Ticket::STATUS_OPEN]);
});

it('prevents a normal user from updating ticket status', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);
    $ticket = Ticket::query()->create([
        'title' => 'T1',
        'description' => 'D1',
        'status' => Ticket::STATUS_OPEN,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user);
    $this->patchJson("/api/tickets/{$ticket->id}/status", ['status' => Ticket::STATUS_CLOSED])
        ->assertStatus(403);
});

it('allows an agent to update ticket status', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);
    $agent = User::factory()->create(['role' => User::ROLE_AGENT]);

    $ticket = Ticket::query()->create([
        'title' => 'T1',
        'description' => 'D1',
        'status' => Ticket::STATUS_OPEN,
        'user_id' => $user->id,
    ]);

    $this->actingAs($agent);
    $this->patchJson("/api/tickets/{$ticket->id}/status", ['status' => Ticket::STATUS_CLOSED])
        ->assertOk()
        ->assertJsonFragment(['status' => Ticket::STATUS_CLOSED]);
});

