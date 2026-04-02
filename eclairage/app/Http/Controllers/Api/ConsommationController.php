<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Compteur;
use App\Models\Consommation;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConsommationController extends Controller
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
        $query = Consommation::with('compteur');

        if ($type = $this->resolveAccessibleType($request)) {
            $query->where('type', $type);
        }

        if ($request->periode) {
            $query->where('periode', $request->periode);
        }

        if ($request->search) {
            $query->where('numero_contrat', 'like', "%{$request->search}%");
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request)
    {
        $type = $request->user()->role === 'admin'
            ? $request->input('type', 'eclairage')
            : $request->user()->access_type;

        $validated = $request->validate([
            'numero_contrat' => ['required', 'exists:compteurs,numero_contrat'],
            'c_kwh' => ['required', 'numeric'],
            'c_dhs' => ['required', 'numeric'],
            'periode' => ['required', 'string'],
            'type' => ['nullable', Rule::in(['eclairage', 'eau'])],
        ]);

        $this->assertCompteurTypeMatches($validated['numero_contrat'], $type);
        $validated['type'] = $type;

        $consommation = Consommation::create($validated);

        return response()->json($consommation, 201);
    }

    public function show(Consommation $consommation)
    {
        $this->authorizeConsommation($consommation, request());

        return response()->json($consommation->load('compteur'));
    }

    public function update(Request $request, Consommation $consommation)
    {
        $this->authorizeConsommation($consommation, $request);

        $type = $request->user()->role === 'admin'
            ? $request->input('type', $consommation->type)
            : $request->user()->access_type;

        $validated = $request->validate([
            'numero_contrat' => ['required', 'exists:compteurs,numero_contrat'],
            'c_kwh' => ['required', 'numeric'],
            'c_dhs' => ['required', 'numeric'],
            'periode' => ['required', 'string'],
            'type' => ['nullable', Rule::in(['eclairage', 'eau'])],
        ]);

        $this->assertCompteurTypeMatches($validated['numero_contrat'], $type);
        $validated['type'] = $type;

        $consommation->update($validated);

        return response()->json($consommation);
    }

    public function destroy(Consommation $consommation)
    {
        $this->authorizeConsommation($consommation, request());

        $consommation->delete();

        return response()->json([
            'message' => 'Consommation deleted successfully',
        ]);
    }

    protected function authorizeConsommation(Consommation $consommation, Request $request): void
    {
        $user = $request->user();

        if ($user && $user->role !== 'admin' && $user->access_type !== $consommation->type) {
            throw new HttpResponseException(response()->json([
                'message' => 'Vous n avez pas acces a ce type de consommation.',
            ], 403));
        }
    }

    protected function assertCompteurTypeMatches(string $numeroContrat, string $type): void
    {
        $compteur = Compteur::where('numero_contrat', $numeroContrat)->first();

        if (! $compteur || $compteur->type !== $type) {
            throw new HttpResponseException(response()->json([
                'message' => 'Le compteur selectionne ne correspond pas au type choisi.',
            ], 422));
        }
    }
}
