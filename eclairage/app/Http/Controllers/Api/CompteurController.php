<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Compteur;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CompteurController extends Controller
{
    protected function resolveAccessibleType(Request $request): ?string
    {
        $user = $request->user();

        if ($user && $user->role !== 'admin') {
            return $user->access_type;
        }

        return $request->query('type');
    }

    public function index(Request $request)
    {
        $query = Compteur::query();

        if ($type = $this->resolveAccessibleType($request)) {
            $query->where('type', $type);
        }

        if ($request->search) {
            $query->where(function ($builder) use ($request) {
                $builder
                    ->where('address', 'like', "%{$request->search}%")
                    ->orWhere('numero_contrat', 'like', "%{$request->search}%");
            });
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request)
    {
        $type = $request->user()->role === 'admin'
            ? $request->input('type', 'eclairage')
            : $request->user()->access_type;

        $validated = $request->validate([
            'numero_contrat' => ['required', 'unique:compteurs,numero_contrat'],
            'numero_compteur' => ['required'],
            'address' => ['required', 'string'],
            'date_creation' => ['nullable', 'date'],
            'type' => ['nullable', Rule::in(['eclairage', 'eau'])],
        ]);

        $validated['type'] = $type;

        $compteur = Compteur::create($validated);

        return response()->json($compteur, 201);
    }

    public function show(Compteur $compteur)
    {
        $this->authorizeCompteur($compteur, request());

        return response()->json($compteur);
    }

    public function update(Request $request, Compteur $compteur)
    {
        $this->authorizeCompteur($compteur, $request);

        $type = $request->user()->role === 'admin'
            ? $request->input('type', $compteur->type)
            : $request->user()->access_type;

        $validated = $request->validate([
            'numero_contrat' => ['required', Rule::unique('compteurs', 'numero_contrat')->ignore($compteur->id)],
            'numero_compteur' => ['required'],
            'address' => ['required', 'string'],
            'date_creation' => ['nullable', 'date'],
            'type' => ['nullable', Rule::in(['eclairage', 'eau'])],
        ]);

        $validated['type'] = $type;

        $compteur->update($validated);

        return response()->json($compteur);
    }

    public function destroy(Compteur $compteur)
    {
        $this->authorizeCompteur($compteur, request());

        $compteur->delete();

        return response()->json([
            'message' => 'Compteur supprime avec succes',
        ]);
    }

    protected function authorizeCompteur(Compteur $compteur, Request $request): void
    {
        $user = $request->user();

        if ($user && $user->role !== 'admin' && $user->access_type !== $compteur->type) {
            throw new HttpResponseException(response()->json([
                'message' => 'Vous n avez pas acces a ce type de compteur.',
            ], 403));
        }
    }
}
