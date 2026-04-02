<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $isFirstUser = User::count() === 0;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $isFirstUser ? 'admin' : 'employee',
            'status' => $isFirstUser ? 'approved' : 'pending',
            'access_type' => $isFirstUser ? 'all' : null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => $isFirstUser
                ? 'Premier compte cree comme administrateur.'
                : 'Inscription enregistree. En attente de validation par l administrateur.',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect.',
            ], 422);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion reussie.',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Deconnexion reussie.',
        ]);
    }

    public function indexUsers()
    {
        return response()->json(
            User::query()
                ->orderByRaw("case when role = 'admin' then 0 else 1 end")
                ->orderBy('name')
                ->get()
        );
    }

    public function updateUserAccess(Request $request, User $user)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved'])],
            'access_type' => ['nullable', Rule::in(['eclairage', 'eau', 'all'])],
        ]);

        if ($user->role === 'admin') {
            $validated['status'] = 'approved';
            $validated['access_type'] = 'all';
        } elseif ($validated['status'] === 'approved' && empty($validated['access_type'])) {
            return response()->json([
                'message' => 'Choisissez un type d acces pour cet employe.',
            ], 422);
        }

        if ($validated['status'] === 'pending') {
            $validated['access_type'] = null;
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Acces utilisateur mis a jour.',
            'user' => $user->fresh(),
        ]);
    }
}
