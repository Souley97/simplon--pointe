<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\ApprenantInscriptionNotification;

use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
      public function login(Request $request)
    {
        // Validation des données de la requête
        $validator = validator($request->all(), [
            'email' => ['required', 'email', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Créer une clé unique pour le throttling basé sur l'email de l'utilisateur et l'adresse IP
        $throttleKey = $request->input('email').'|'.$request->ip();

        // Vérifier si trop de tentatives ont été effectuées
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'message' => "Trop de tentatives de connexion. Réessayez dans $seconds secondes.",
            ], 429); // Code de statut HTTP 429 pour "Trop de requêtes"
        }

        // Vérifier si l'utilisateur existe
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            RateLimiter::hit($throttleKey, 10); // Incrémenter le compteur de tentatives
            return response()->json([
                'message' => 'Utilisateur non trouvé',
            ], 404);
        }

        // Vérifier si le mot de passe est correct
        if (!Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 60); // Incrémenter le compteur en cas de mot de passe incorrect
            return response()->json([
                'message' => 'Mot de passe incorrect',
            ], 401);
        }

        // Authentification réussie, réinitialiser les tentatives
        RateLimiter::clear($throttleKey); // Réinitialiser le compteur de tentatives réussie

        // Générer le token JWT
        $token = auth()->guard('api')->login( $user);

        return response()->json(data: [
            'access_token' => $token,
            'user' => $user,
            'token_type' => 'bearer',
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
            'photo_profile' => ['nullable', 'string'], // Chemin ou URL pour la photo de profil
            'promotion_id' => ['nullable', 'exists:promos,id'] // ID de la promotion à laquelle l'apprenant sera affecté
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $password = $request->prenom . Str::random(4); // Par exemple: "prenomXYZ"
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
        $password = $request->prenom . Str::random(4); // Par exemple: "prenomXYZ"
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
        $password = $request->prenom . Str::random(4); // Par exemple: "prenomXYZ"
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
