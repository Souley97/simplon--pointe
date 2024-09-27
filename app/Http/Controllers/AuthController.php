<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\ApprenantInscriptionNotification;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validation des données
        $validator = validator($request->all(), [
            'email' => ['required', 'email', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Vérifier si l'utilisateur existe
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non trouvé',
            ], 404); // User not found
        }

        // Vérifier si le mot de passe est correct
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Mot de passe incorrect',
            ], 401); // Incorrect password
        }

        // Authentification réussie, générer le token
        $token = auth()->guard('api')->login($user);

        // Obtenir les rôles de l'utilisateur
        $roles = $user->getRoleNames();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'roles' => $roles,
            'user' => $user,
            'expires_in' => auth()->guard('api')->factory()->getTTL() * 60, // Expiration en secondes
        ]);
    }

    // logout
      public function logout()
    {
        Auth::guard('api')->logout();
        return response()->json(['message' => 'Déconnexion réussie.'], 200);
    }
    public function inscrireFormateur(Request $request)
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

        // Création de l'utilisateur avec le mot de passe
        $password = $request->password; // On garde une version du mot de passe non crypté
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'adresse' => $request->adresse,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'password' => Hash::make($password), // Crypter le mot de passe
            'photo_profile' => $request->photo_profile,
            'sexe' => $request->sexe,
        ]);

        // Générer le matricule en combinant le prénom et un numéro aléatoire
        $user->matricule = Str::slug($request->prenom) . '-' . Str::random(5);
        $user->save();

        // Vérifier si le rôle d'apprenant existe, sinon le créer
        $role = Role::firstOrCreate(['name' => 'Formateur']);
        $user->assignRole($role);

        // // Assigner à une promotion si l'ID de promotion est fourni
        // if ($request->has('promotion_id')) {
        //     $user->promotions()->attach($request->promotion_id);
        // }

        // Envoi de la notification par email avec les informations d'accès
        $user->notify(new ApprenantInscriptionNotification($user, $password));

        return response()->json([
            'success' => true,
            'message' => 'Formateur inscrit avec succès et notification envoyée par email',
            'user' => $user
        ], 201);
    }

    public function inscrireChefDeProjet(Request $request)
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

        // Création de l'utilisateur avec le mot de passe
        $password = $request->password; // On garde une version du mot de passe non crypté
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'adresse' => $request->adresse,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'password' => Hash::make($password), // Crypter le mot de passe
            'photo_profile' => $request->photo_profile,
            'sexe' => $request->sexe,
        ]);

        // Générer le matricule en combinant le prénom et un numéro aléatoire
        $user->matricule = Str::slug($request->prenom) . '-' . Str::random(5);
        $user->save();

        // Vérifier si le rôle d'apprenant existe, sinon le créer
        $role = Role::firstOrCreate(['name' => 'ChefDeProjet']);
        $user->assignRole($role);

        // // Assigner à une promotion si l'ID de promotion est fourni
        // if ($request->has('promotion_id')) {
        //     $user->promotions()->attach($request->promotion_id);
        // }

        // Envoi de la notification par email avec les informations d'accès
        $user->notify(new ApprenantInscriptionNotification($user, $password));

        return response()->json([
            'success' => true,
            'message' => 'Chef de projet inscrit avec succès et notification envoyée par email',
            'user' => $user
        ], 201);
    }

    public function inscrireVigile(Request $request)
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

        // Création de l'utilisateur avec le mot de passe
        $password = $request->password; // On garde une version du mot de passe non crypté
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'adresse' => $request->adresse,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'password' => Hash::make($password), // Crypter le mot de passe
            'photo_profile' => $request->photo_profile,
            'sexe' => $request->sexe,
        ]);

        // Générer le matricule en combinant le prénom et un numéro aléatoire
        $user->matricule = Str::slug($request->prenom) . '-' . Str::random(5);
        $user->save();

        // Vérifier si le rôle d'apprenant existe, sinon le créer
        $role = Role::firstOrCreate(['name' => 'Vigile']);
        $user->assignRole($role);

        // // Assigner à une promotion si l'ID de promotion est fourni
        // if ($request->has('promotion_id')) {
        //     $user->promotions()->attach($request->promotion_id);
        // }

        // Envoi de la notification par email avec les informations d'accès
        $user->notify(new ApprenantInscriptionNotification($user, $password));

        return response()->json([
            'success' => true,
            'message' => 'Vigile inscrit avec succès et notification envoyée par email',
            'user' => $user
        ], 201);
    }
}
