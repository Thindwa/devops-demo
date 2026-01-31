<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(
            User::query()
                ->select(['id', 'name', 'email', 'role', 'created_at'])
                ->orderBy('id')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:user,agent,admin'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return response()->json(
            $user->only(['id', 'name', 'email', 'role', 'created_at']),
            201
        );
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'password' => ['sometimes', 'string', 'min:8'],
            'role' => ['sometimes', 'in:user,agent,admin'],
        ]);

        if (array_key_exists('password', $data)) {
            $data['password'] = Hash::make($data['password']);
        }

        if (empty($data)) {
            return response()->json(['message' => 'No changes provided'], 422);
        }

        $user->update($data);

        return response()->json(
            $user->fresh()->only(['id', 'name', 'email', 'role', 'created_at'])
        );
    }

    public function destroy(Request $request, User $user)
    {
        /** @var User $actor */
        $actor = $request->user();
        if ($actor && $actor->id === $user->id) {
            return response()->json(['message' => 'Cannot delete your own account'], 422);
        }

        $user->delete();

        return response()->json(['ok' => true]);
    }
}

