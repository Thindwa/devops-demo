<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    private function canAccessTicket(User $user, Ticket $ticket): bool
    {
        if ($user->role === User::ROLE_USER) {
            return $ticket->user_id === $user->id;
        }

        return true;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Ticket::query()->with('user:id,name,email,role')->latest();

        if ($user->role === User::ROLE_USER) {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->get());
    }

    public function show(Request $request, Ticket $ticket)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$this->canAccessTicket($user, $ticket)) {
            // Hide existence from normal users.
            abort(404);
        }

        return response()->json($ticket->load('user:id,name,email,role'));
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

    public function update(Request $request, Ticket $ticket)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$this->canAccessTicket($user, $ticket)) {
            abort(404);
        }

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
        ]);

        if (empty($data)) {
            return response()->json(['message' => 'No changes provided'], 422);
        }

        $ticket->update($data);

        return response()->json($ticket->load('user:id,name,email,role'));
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,closed'],
        ]);

        $ticket->update(['status' => $data['status']]);

        return response()->json($ticket->load('user:id,name,email,role'));
    }

    public function destroy(Request $request, Ticket $ticket)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$this->canAccessTicket($user, $ticket)) {
            abort(404);
        }

        $ticket->delete();

        return response()->json(['ok' => true]);
    }
}

