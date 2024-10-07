<?php

namespace App\Http\Controllers;

use App\Models\Pointage;
use App\Models\Justificatif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreJustificatifRequest;
use App\Http\Requests\UpdateJustificatifRequest;


class JustificatifController extends Controller
{
    public function justifierAbsence(Request $request, $pointageId)
    {
        // Validation des données du formulaire
        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:20048', // Fichier requis, types acceptés : pdf, jpg, etc.
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Récupérer le pointage lié à l'absence
        $pointage = Pointage::findOrFail($pointageId);

        // Vérifier que le type de pointage est bien "absence"
        if ($pointage->type !== 'absence') {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les pointages marqués comme absences peuvent être justifiés.',
            ], 400);
        }

        // Gestion de l'upload du document
        // Store the document in the storage directory and get the relative path
        $documentPath = $request->file('document')->store('justificatifs', 'public');

        // Create a full URL to access the document
        $documentUrl = asset('storage/' . $documentPath);

        // Créer un justificatif
        Justificatif::create([
            'date' => now()->toDateString(),
            'description' => $request->input('description'),
            'document' => $documentPath, // Store the relative path for database
            'pointage_id' => $pointage->id,
        ]);

        // Mettre à jour le pointage avec un motif si nécessaire
        $pointage->update([
            'motif' => $request->input('description') ?? 'Justification fournie',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Justificatif enregistré avec succès.',
            'document_url' => $documentUrl, // Return the document URL if needed
        ]);
    }

public function VoirJustifierAbsence(Request $request, $pointageId)
{
    // Authenticate the user (optional, depending on your application setup)
    $user = Auth::user();

    // Fetch the justification for the given pointageId
    $justification = Justificatif::where('pointage_id', $pointageId)
        ->first();

    if (!$justification) {
        return response()->json([
            'message' => 'Aucune justification trouvée pour cet identifiant.',
        ], 404);
    }

    // Return the justification data
        return response()->json([
            'justification' => [
                'description' => $justification->description,
                'document' => $justification->document, // Utilisez 'document' au lieu de 'document_path'
                'created_at' => $justification->created_at,
            ],
        ], 200);

}

}
