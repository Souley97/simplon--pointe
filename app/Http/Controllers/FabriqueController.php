<?php

namespace App\Http\Controllers;

use App\Models\Fabrique;
use Illuminate\Http\Request;
use App\Http\Requests\StoreFabriqueRequest;
use App\Http\Requests\UpdateFabriqueRequest;

class FabriqueController extends Controller
{
   /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fabriques = Fabrique::all();
        return response()->json($fabriques);
    }

    // Créer une nouvelle Fabrique
    public function store(StoreFabriqueRequest $request)
    {


        $fabrique = Fabrique::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Fabrique créée avec succès.',
            'fabrique' => $fabrique,
        ], 201);
    }

    // Afficher une fabrique spécifique
    public function show(Fabrique $fabrique)
    {
        return response()->json($fabrique);
    }

    // Mettre à jour une Fabrique
    public function update(Request $request, Fabrique $fabrique)
    {
        $request->validate([
            'nom' => 'required|string|max:50',

        ]);

        $fabrique->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Fabrique mise à jour avec succès.',
            'fabrique' => $fabrique,
        ]);
    }

    // Supprimer une Fabrique
    public function destroy(Fabrique $fabrique)
    {
        $fabrique->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fabrique supprimée avec succès.',
        ]);
    }

    // les promos dans un Fabrique

    public function promos($id)
    {
        // Récupérer la Fabrique avec ses promos
        $fabrique = Fabrique::with('promos')->find($id);

        // Vérifier si la Fabrique existe
        if (!$fabrique) {
            return response()->json([
                'success' => false,
                'message' => 'Fabrique non trouvée.',
            ], 404);
        }

        // Retourner les promos associées à la Fabrique
        return response()->json([
            'success' => true,
            'fabrique' => $fabrique->nom,
            'promos' => $fabrique->promos, // Liste des promos de la Fabrique
        ]);
    }
}
