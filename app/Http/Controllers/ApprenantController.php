<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Promo;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
// Xlsx
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use App\Notifications\ApprenantInscriptionNotification;
use PhpOffice\PhpSpreadsheet\Reader\Exception as SpreadsheetException;

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
        $role = Role::firstOrCreate(['name' => 'Apprenant']);
        $user->assignRole($role);

        // Assigner à une promotion si l'ID de promotion est fourni
        if ($request->has('promotion_id')) {
            $user->promotions()->attach($request->promotion_id);
        }

        // Envoi de la notification par email avec les informations d'accès
        $user->notify(new ApprenantInscriptionNotification($user, $password));

        return response()->json([
            'success' => true,
            'message' => 'Apprenant inscrit avec succès et notification envoyée par email',
            'user' => $user
        ], 201);
    }

    public function inscrireApprenantsExcel(Request $request)
    {
        // Validation du fichier Excel et de l'ID de la promo
        $validator = Validator::make($request->all(), [
            'file' => ['required', 'mimes:xls,xlsx'], // Seuls les fichiers Excel sont autorisés
            'promo_id' => ['required', 'exists:promos,id'], // Assurez-vous que la promo existe
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Récupérer la promo à partir de l'ID
        $promo = Promo::findOrFail($request->promo_id);

        // Charger le fichier Excel
        $file = $request->file('file');
        $path = $file->getRealPath(); // Obtenir le chemin du fichier

        try {
            // Lire le contenu du fichier Excel
            $reader = new Xlsx();
            $spreadsheet = $reader->load($path);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            // Parcourir les données du fichier Excel
            foreach ($sheetData as $index => $row) {
                if ($index == 0) {
                    // Ignorer la première ligne (en-têtes)
                    continue;
                }

                // Vérifier si toutes les colonnes nécessaires sont présentes dans chaque ligne
                if (count($row) < 6) {
                    continue; // Ignorer la ligne si elle est incomplète
                }

                // Vérifier si l'utilisateur existe déjà avec le même email
                $existingUser = User::where('email', $row[4])->first();
                if ($existingUser) {
                    // Ajouter l'utilisateur existant à la promo s'il n'y est pas déjà
                    if (!$promo->apprenants()->where('user_id', $existingUser->id)->exists()) {
                        $promo->apprenants()->attach($existingUser->id);
                    }
                    continue; // Ignorer la création si l'utilisateur existe déjà
                }

                // Générer un mot de passe aléatoire pour l'utilisateur
                $password = Str::random(8);

                // Créer un nouvel utilisateur avec les informations du fichier Excel
                $user = User::create([
                    'nom' => $row[0],
                    'prenom' => $row[1],
                    'adresse' => $row[2],
                    'telephone' => $row[3],
                    'email' => $row[4],
                    'password' => Hash::make($password), // Crypter le mot de passe
                    'sexe' => $row[5],
                ]);

                // Générer un matricule pour l'utilisateur
                $user->matricule = Str::slug($row[1]) . '-' . Str::random(5);
                $user->save();

                // Assigner le rôle "Apprenant" à l'utilisateur
                $role = Role::firstOrCreate(['name' => 'Apprenant']);
                $user->assignRole($role);

                // Associer l'utilisateur à la promo sélectionnée
                $promo->apprenants()->attach($user->id);

                // Envoyer un email de notification à l'utilisateur avec ses informations d'accès
                $user->notify(new ApprenantInscriptionNotification($user, $password));
            }

            return response()->json([
                'success' => true,
                'message' => 'Les apprenants ont été inscrits avec succès dans la promotion.',
            ]);

        } catch (SpreadsheetException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la lecture du fichier Excel : ' . $e->getMessage(),
            ], 500);
        }
    }


}
