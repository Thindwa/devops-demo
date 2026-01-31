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

it('allows a user to update their own ticket title/description', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);
    $ticket = Ticket::query()->create([
        'title' => 'Old',
        'description' => 'Old desc',
        'status' => Ticket::STATUS_OPEN,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user);
    $this->patchJson("/api/tickets/{$ticket->id}", [
        'title' => 'New',
        'description' => 'New desc',
    ])->assertOk()
        ->assertJsonFragment(['title' => 'New'])
        ->assertJsonFragment(['description' => 'New desc']);
});

it('prevents a user from updating another users ticket', function () {
    $owner = User::factory()->create(['role' => User::ROLE_USER]);
    $other = User::factory()->create(['role' => User::ROLE_USER]);

    $ticket = Ticket::query()->create([
        'title' => 'T1',
        'description' => 'D1',
        'status' => Ticket::STATUS_OPEN,
        'user_id' => $owner->id,
    ]);

    $this->actingAs($other);
    $this->patchJson("/api/tickets/{$ticket->id}", ['title' => 'Hack'])->assertStatus(404);
});

it('allows a user to delete their own ticket', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);
    $ticket = Ticket::query()->create([
        'title' => 'T1',
        'description' => 'D1',
        'status' => Ticket::STATUS_OPEN,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user);
    $this->deleteJson("/api/tickets/{$ticket->id}")
        ->assertOk()
        ->assertJson(['ok' => true]);
});

it('prevents a user from deleting another users ticket', function () {
    $owner = User::factory()->create(['role' => User::ROLE_USER]);
    $other = User::factory()->create(['role' => User::ROLE_USER]);

    $ticket = Ticket::query()->create([
        'title' => 'T1',
        'description' => 'D1',
        'status' => Ticket::STATUS_OPEN,
        'user_id' => $owner->id,
    ]);

    $this->actingAs($other);
    $this->deleteJson("/api/tickets/{$ticket->id}")->assertStatus(404);
});

it('allows an agent to delete any ticket', function () {
    $owner = User::factory()->create(['role' => User::ROLE_USER]);
    $agent = User::factory()->create(['role' => User::ROLE_AGENT]);

    $ticket = Ticket::query()->create([
        'title' => 'T1',
        'description' => 'D1',
        'status' => Ticket::STATUS_OPEN,
        'user_id' => $owner->id,
    ]);

    $this->actingAs($agent);
    $this->deleteJson("/api/tickets/{$ticket->id}")
        ->assertOk()
        ->assertJson(['ok' => true]);
});

