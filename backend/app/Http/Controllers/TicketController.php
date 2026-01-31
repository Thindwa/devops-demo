<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Ticket::query()->with('user:id,name,email,role')->latest();

        if ($user->role === User::ROLE_USER) {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        $ticket = Ticket::query()->create([
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => Ticket::STATUS_OPEN,
            'user_id' => $user->id,
        ]);

        return response()->json($ticket->load('user:id,name,email,role'), 201);
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,closed'],
        ]);

        $ticket->update(['status' => $data['status']]);

        return response()->json($ticket->load('user:id,name,email,role'));
    }
}

