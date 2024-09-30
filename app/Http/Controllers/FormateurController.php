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
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use App\Notifications\ApprenantInscriptionNotification;
use PhpOffice\PhpSpreadsheet\Reader\Exception as SpreadsheetException;

class FormateurController extends Controller
{
    public function afficherPointagesPromo(Request $request)
    {
        $user = auth()->user();
        $mois = $request->input('mois');
        $annee = $request->input('annee');
        $date = "2024-09-10"; // Récupère une date spécifique si elle est fournie

        $semaine = $request->input('semaine'); // Nouvelle entrée pour la semaine
        if (!$mois || !$annee) {
            return response()->json([
                'success' => false,
                'message' => 'Les paramètres mois et année sont requis.',
            ], 400);
        }
        // Récupérer l'ID de la promotion depuis les paramètres de la requête GET
        $promotionId = $request->query('promo_id');
// Si un numéro de semaine est fourni, calculer les dates de début et de fin de la semaine
if ($semaine) {
    $date_debut = Carbon::now()->setISODate($annee, $semaine)->startOfWeek();
    $date_fin = Carbon::now()->setISODate($annee, $semaine)->endOfWeek();
} else {
    // Si aucune semaine n'est fournie, utiliser le mois et l'année pour récupérer les dates de début et de fin du mois
    $date_debut = Carbon::createFromDate($annee, $mois, 1)->startOfMonth();
    $date_fin = Carbon::createFromDate($annee, $mois, 1)->endOfMonth();
}

        // Validation des données d'entrée
        $validator = validator(['promo_id' => $promotionId], [
            'promo_id' => ['required', 'exists:promos,id'], // Vérifie que la promo existe
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Récupérer la promotion avec sa date de début
        $promotion = Promo::find($promotionId);

        // Si la promotion n'est pas trouvée (par sécurité)
        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'La promotion n\'existe pas.',
            ], 404);
        }

        // Récupérer les utilisateurs (apprenants et formateurs) qui appartiennent à la promotion
        $users = User::whereHas('promos', function ($query) use ($promotionId) {
            $query->where('promos.id', $promotionId);
        })
        ->whereHas('roles', function ($query) {
            $query->whereIn('name', ['Apprenant', 'Formateur']);
        })
        ->pluck('id'); // Récupérer uniquement les IDs des utilisateurs

        // Récupérer les pointages depuis la date de début de la promotion jusqu'à aujourd'hui
        $pointages = Pointage::whereIn('user_id', $users)
            ->whereBetween('date', [$promotion->date_debut, now()->toDateString()]) // Filtrer entre la date de début et aujourd'hui
            ->with('user') // Charger les informations de l'utilisateur
            ->get();

        // Vérifier si des pointages ont été trouvés
        if ($pointages->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun pointage trouvé pour cette promotion depuis sa date de début.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pointages des apprenants et formateurs récupérés avec succès.',
            'pointages' => $pointages,
        ]);
    }
    public function afficherPointagesPromos(Request $request)
    {
        $promotionId = $request->query('promo_id');
        $dateSelection = $request->query('date');

        $validator = validator([
            'promo_id' => $promotionId,
            'date' => $dateSelection,
        ], [
            'promo_id' => ['required', 'exists:promos,id'],
            'date' => ['required', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $promotion = Promo::find($promotionId);

        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'La promotion n\'existe pas.',
            ], 404);
        }

        $apprenants = User::whereHas('promos', function ($query) use ($promotionId) {
            $query->where('promos.id', $promotionId);
        })
        ->whereHas('roles', function ($query) {
            $query->whereIn('name', ['Apprenant','Formateur']);
        })
        ->get();

        if ($apprenants->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Aucun apprenant trouvé pour cette promotion.',
                'apprenants_avec_pointage' => [],
                'apprenants_sans_pointage' => [],
                'date' => $dateSelection,
            ]);
        }

        $pointages = Pointage::whereIn('user_id', $apprenants->pluck('id'))
            ->where('date', $dateSelection)
            ->with('user')
            ->get();

        $apprenantsAvecPointages = $pointages->pluck('user_id')->unique();
        $apprenantsSansPointages = $apprenants->whereNotIn('id', $apprenantsAvecPointages);

        return response()->json([
            'success' => true,
            'message' => 'Pointages récupérés avec succès.',
            'apprenants_avec_pointage' => $pointages,
            'apprenants_sans_pointage' => $apprenantsSansPointages->values(),
            'date' => $dateSelection,
        ]);
    }


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
            'promotion_id' => ['nullable', 'exists:promos,id'] // ID de la promotion
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
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

            // Générer le matricule
            $user->matricule = Str::slug($request->prenom) . '-' . Str::random(5);
            $user->save();

            // Vérifier si le rôle d'apprenant existe, sinon le créer
            $role = Role::firstOrCreate(['name' => 'Apprenant']);
            $user->assignRole($role);

            // Assigner à une promotion si l'ID de promotion est fourni
            if ($request->has('promotion_id')) {
                $user->promotions()->attach($request->promotion_id);
            }

            // Envoi de la notification par email
            $user->notify(new ApprenantInscriptionNotification($user, $password));

            return response()->json([
                'success' => true,
                'message' => 'Apprenant inscrit avec succès et notification envoyée par email',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            // Retourner une réponse d'erreur si une exception survient
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
    //

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

//  liste des formateurs et ChefDeProjet
public function ListeFormateurs()
{
    // Récupérer les formateurs avec leur rôle et leurs promos
    $formateurs = User::whereHas('roles', function ($query) {
        $query->whereIn('name', ['Formateur', 'ChefDeProjet']); // Récupérer uniquement les formateurs ici
    })
    ->with('roles') // Charger les rôles associés
    ->withCount(['promosForamateur','promosChefDeProjet'])  // Comptabiliser le nombre de promotions associées à chaque formateur
    ->with(['promosForamateur','promosChefDeProjet'])  // Comptabiliser le nombre de promotions associées à chaque formateur
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
public function getPromotions() {
    // Fetch all formateurs and chefs de projet
    $formateurs = User::whereHas('roles', function ($query) {
        $query->whereIn('name', ['Formateur', 'ChefDeProjet']);
    })
    ->with('roles')
    ->with(['promosForamateur', 'promosChefDeProjet'])
    ->get();

    $formateurs->each(function($formateur) {
        $formateur->role = $formateur->roles->first()->name;
    });

    if ($formateurs->isEmpty()) {
        return response()->json([
            'success' => true,
            'message' => 'Aucun formateur trouvé.',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Formateurs et chefs de projet récupérés avec succès.',
        'formateurs' => $formateurs,
    ]);
}






}
