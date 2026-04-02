<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Non authentifie.'], 401);
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if ($user->status !== 'approved' || empty($user->access_type)) {
            return response()->json([
                'message' => 'Votre compte est en attente de validation par l administrateur.',
            ], 403);
        }

        return $next($request);
    }
}
