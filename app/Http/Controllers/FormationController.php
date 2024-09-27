<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Http\Requests\StoreFormationRequest;
use App\Http\Requests\UpdateFormationRequest;

use Illuminate\Http\Request;

class FormationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $formations = Formation::withCount('promos')->with('promos')->get();

        return response()->json($formations);
    }

    // Créer une nouvelle formation
    public function store(Request $request)
    {


        $formation = Formation::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Formation créée avec succès.',
            'formation' => $formation,
        ], 201);
    }

    // Afficher une formation spécifique
    public function show(Formation $formation)
    {
        return response()->json($formation);
    }

    // Mettre à jour une formation
    public function update(Request $request, Formation $formation)
    {
        $request->validate([
            'nom' => 'required|string|max:50',

        ]);

        $formation->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Formation mise à jour avec succès.',
            'formation' => $formation,
        ]);
    }

    // Supprimer une formation
    public function destroy(Formation $formation)
    {
        $formation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Formation supprimée avec succès.',
        ]);
    }

    // les promos dans un formation

    public function promos($id)
    {
        // Récupérer la formation avec ses promos
        $formation = Formation::with('promos')->find($id);

        // Vérifier si la formation existe
        if (!$formation) {
            return response()->json([
                'success' => false,
                'message' => 'Formation non trouvée.',
            ], 404);
        }

        // Retourner les promos associées à la formation
        return response()->json([
            'success' => true,
            'formation' => $formation->nom,
            'promos' => $formation->promos, // Liste des promos de la formation
        ]);
    }

}
