<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function updateInformation(Request $request)
    {
        // Validation des données
        $validator = validator($request->all(), [
            'adresse' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:15'],
            'photo_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Optionnel et non obligatoire
            'password' => ['nullable', 'string', 'min:8'] // Mot de passe (optionnel)
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Trouver l'utilisateur actuellement connecté
        $user = auth()->user();

        // Mise à jour de la photo de profil
        if ($request->hasFile('photo_profile')) {
            // Suppression de l'ancienne photo_profile s'il y en a une
            if ($user->photo_profile) {
                Storage::disk('public')->delete($user->photo_profile);
            }

            // Stockage de la nouvelle photo_profile
            $photo_profile = $request->file('photo_profile');
            $user->photo_profile = $photo_profile->store('profile', 'public');
        }

        // Mise à jour des informations
        $user->update([
            'adresse' => $request->adresse,
            'telephone' => $request->telephone,
            'photo_profile' => $user->photo_profile, // Assurer que la photo_profile est bien mise à jour
            'password' => $request->password ? Hash::make($request->password) : $user->password, // Chiffrement du mot de passe uniquement s'il est présent
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Informations mises à jour avec succès',
            'user' => $user
        ], 200);
    }

    public function chefsProjet()
{
    $chefs = User::whereHas('roles', function($query) {
        $query->where('name', 'ChefDeProjet');
    })->get(); // Récupère tous les utilisateurs ayant le rôle de Chef de Projet
    return response()->json($chefs);
}

public function personnelListe()
{
    // Récupérer les formateurs avec leur rôle et leurs promos
    $formateurs = User::whereHas('roles', function ($query) {
        $query->whereIn('name', ['Formateur', 'ChefDeProjet', 'Vigile']); // Récupérer uniquement les formateurs ici
    })
    ->with('roles') // Charger les rôles associés
    ->get();

    // Ajouter les rôles sous forme de chaîne de caractères à chaque formateur
    $formateurs->each(function($formateur) {
        $formateur->role = $formateur->roles->first()->name; // Supposer que chaque formateur n'a qu'un rôle
    });

    // Vérifier si des formateurs sont présents
    if ($formateurs->isEmpty()) {
        return response()->json([
            'success' => true,
            'message' => 'Aucun formateur trouvé.',
        ], 404);
    }

    // Récupérer un chef de projet séparément
    $chefDeProjet = User::whereHas('roles', function ($query) {
        $query->where('name', 'ChefDeProjet');
    })->with('roles')->first();

    // Vérifier si un chef de projet est présent
    if (!$chefDeProjet) {
        return response()->json([
            'success' => true,
            'message' => 'Aucun chef de projet trouvé.',
            'formateurs' => $formateurs,
        ], 404);
    }

    // Retourner les formateurs et le chef de projet
    return response()->json([
        'success' => true,
        'message' => 'Formateurs et chef de projet récupérés avec succès.',
        'formateurs' => $formateurs,
    ]);
}
}
