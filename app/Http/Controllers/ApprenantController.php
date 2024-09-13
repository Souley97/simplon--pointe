<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
class ApprenantController extends Controller
{
    public function inscrireApprenant(Request $request)
    {
        // Validation des données
        $validator = validator($request->all(), [
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'adresse' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'photo_profile' => ['nullable', 'string'], // Chemin ou URL pour la photo de profil
            'promotion_id' => ['nullable', 'exists:promos,id'] // ID de la promotion à laquelle l'apprenant sera affecté
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Création de l'utilisateur
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'adresse' => $request->adresse,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'photo_profile' => $request->photo_profile,
            'sexe' => $request->sexe,


        ]);

        // Générer le matricule en combinant le prénom et un numéro aléatoire
        $user->matricule = Str::slug($request->prenom) . '-' . Str::random(5);
        $user->save();

        // Vérifier si le rôle d'apprenant existe, sinon le créer
        $role = Role::firstOrCreate(['name' => 'Apprenant']);
        $user->assignRole($role);

        // Assigner à une promotion si l'ID de promotion est fourni
        if ($request->has('promotion_id')) {
            $user->promotions()->attach($request->promotion_id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Apprenant inscrit avec succès',
            'user' => $user
        ], 201);
    }
}
