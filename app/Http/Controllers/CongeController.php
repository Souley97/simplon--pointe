<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use Illuminate\Http\Request;

class CongeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:congé,permission,absence',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'motif' => 'nullable|string',
        ], [
            'type.required' => 'Le type de congé est obligatoire.',
            'type.in' => 'Le type doit être congé, permission ou absence.',
            'date_debut.required' => 'La date de début est obligatoire.',
            'date_debut.date' => 'La date de début doit être une date valide.',
            'date_fin.date' => 'La date de fin doit être une date valide.',
            'date_fin.after_or_equal' => 'La date de fin doit être après ou égale à la date de début.',
        ]);


        $conge = Conge::create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'motif' => $request->motif,
            'status' => 'en attente',
        ]);

        return response()->json($conge, 201);
    }

    // Récupérer toutes les conges d'un utilisateur
    public function myConge()
    {
        $conges = Conge::where('user_id', auth()->id())->get();
        return response()->json($conges);
    }
    public function index()
    {
        $conges = Conge::get();
        return response()->json($conges);
    }
    // Supprimer une conge
    public function destroy($id)
    {
        try {
            $conge = Conge::findOrFail($id);
            
            $conge->delete();

            return response()->json(['message' => 'Congé supprimé avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression du congé.'], 500);
        }
    }

    // Approuver ou rejeter une conge
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approuvée,rejetée',
        ]);

        try {
            $conge = Conge::findOrFail($id);
            $conge->update(['status' => $request->status]);

            return response()->json($conge);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la mise à jour du statut du congé.'], 500);
        }
    }

}
