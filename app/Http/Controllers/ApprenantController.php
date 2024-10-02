<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Promo;
use App\Models\Pointage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
// Xlsx
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use App\Notifications\ApprenantInscriptionNotification;
use PhpOffice\PhpSpreadsheet\Reader\Exception as SpreadsheetException;
use App\Mail\ApprenantInscritMail;

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
            'photo_profile' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'promotion_id' => ['nullable', 'exists:promos,id'] // ID de la promotion
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Handle profile picture upload
            $photo_profile = null;
            if ($request->hasFile('photo_profile')) {
                $photo_profile = $request->file('photo_profile')->store('profiles', 'public');
            }

            // Create the user with the password
            $password = $request->prenom . Str::random(4); // Example: "prenomXYZ"
            $user = User::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'adresse' => $request->adresse,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'password' => Hash::make($password), // Encrypt the password
                'photo_profile' => $photo_profile, // Store the photo path if available
                'sexe' => $request->sexe,
            ]);

            // Generate and save the matricule
            $user->matricule = Str::slug($request->prenom) . '-' . Str::random(5);
            $user->save();

            // Assign role and promotion if necessary
            $role = Role::firstOrCreate(['name' => 'Apprenant']);
            $user->assignRole($role);

            if ($request->has('promotion_id')) {
                $user->promotions()->attach($request->promotion_id);
            }

            // Send email notification
            $user->notify(new ApprenantInscriptionNotification($user, $password));

            return response()->json([
                'success' => true,
                'message' => 'Apprenant inscrit avec succès et notification envoyée par email',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue : ' . $e->getMessage()
            ], 500);
        }
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

   // MesPointages apprenant connecté avec option de filtrage par mois ou par semaine
public function MesPointages(Request $request)
{
    $user = auth()->user();
    $mois = $request->input('mois');
    $annee = $request->input('annee');
    $semaine = $request->input('semaine'); // Nouvelle entrée pour la semaine

    // Vérification des paramètres mois, année ou semaine
    if (!$mois || !$annee) {
        return response()->json([
            'success' => false,
            'message' => 'Les paramètres mois et année sont requis.',
        ], 400);
    }

    // Si un numéro de semaine est fourni, calculer les dates de début et de fin de la semaine
    if ($semaine) {
        $date_debut = Carbon::now()->setISODate($annee, $semaine)->startOfWeek();
        $date_fin = Carbon::now()->setISODate($annee, $semaine)->endOfWeek();
    } else {
        // Si aucune semaine n'est fournie, utiliser le mois et l'année pour récupérer les dates de début et de fin du mois
        $date_debut = Carbon::createFromDate($annee, $mois, 1)->startOfMonth();
        $date_fin = Carbon::createFromDate($annee, $mois, 1)->endOfMonth();
    }

    // Récupérer les pointages de l'utilisateur pour la période sélectionnée
    $pointages = Pointage::where('user_id', $user->id)
        ->whereBetween('date', [$date_debut, $date_fin])
        ->get();

    if ($pointages->isEmpty()) {
        return response()->json([
            'success' => true,
            'message' => 'Aucun pointage trouvé pour cette période.',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Pointages récupérés avec succès.',
        'pointages' => $pointages,
    ]);
}



}
