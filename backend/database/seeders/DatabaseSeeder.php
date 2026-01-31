<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN]
        );

        $agent = User::query()->firstOrCreate(
            ['email' => 'agent@example.com'],
            ['name' => 'Agent', 'password' => Hash::make('password'), 'role' => User::ROLE_AGENT]
        );

        $user = User::query()->firstOrCreate(
            ['email' => 'user@example.com'],
            ['name' => 'User', 'password' => Hash::make('password'), 'role' => User::ROLE_USER]
        );

        // Seed a couple of tickets for quick demo.
        Ticket::query()->firstOrCreate(
            ['title' => 'Cannot login', 'user_id' => $user->id],
            ['description' => 'I cannot login to the system', 'status' => Ticket::STATUS_OPEN]
        );

        Ticket::query()->firstOrCreate(
            ['title' => 'Printer is broken', 'user_id' => $user->id],
            ['description' => 'Office printer shows error code E42', 'status' => Ticket::STATUS_IN_PROGRESS]
        );
    }
}
