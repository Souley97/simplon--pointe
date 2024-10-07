<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Promo;
use App\Models\Pointage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePointageRequest;
use App\Http\Requests\UpdatePointageRequest;

class PointageController extends Controller
{
    /**
     * Display a listing of the resource.
     */


     public function pointageArrivee(Request $request)
     {
         // Utilisateur connecté
         $vigile = auth()->user();

         // Vérifier si l'utilisateur est un vigile
         if (!$vigile->hasRole('Vigile')) {
             return response()->json([
                 'success' => false,
                 'message' => 'Vous n\'êtes pas autorisé à accéder à cette section.',
             ], 403);
         }

         // Validation des données d'entrée
         $validator = validator($request->all(), [
             'matricule' => ['required', 'exists:users,matricule'],
         ]);

         if ($validator->fails()) {
             return response()->json([
                 'success' => false,
                 'errors' => $validator->errors(),
             ], 422);
         }

         // Récupérer l'utilisateur
         $user = User::where('matricule', $request->matricule)->firstOrFail();

         // Vérifier s'il y a déjà un pointage pour aujourd'hui
         $pointage = Pointage::where('user_id', $user->id)
                             ->where('date', now()->toDateString())
                             ->first();

         // Si l'utilisateur a déjà pointé son arrivée ou est marqué présent/retard, renvoyer un message d'erreur
         if ($pointage && $pointage->type != 'absence') {
             return response()->json([
                 'success' => false,
                 'message' => 'L\'utilisateur a déjà pointé pour cette date.',
             ], 400);
         }


         // Déterminer l'heure actuelle et le type de pointage
         $heure_actuelle = now();
         $heure_limite = now()->setTime(9, 0);
         $type = $heure_actuelle->greaterThan($heure_limite) ? 'retard' : 'present';

         // Si l'utilisateur a été marqué comme absence, mettre à jour le pointage existant
         if ($pointage && $pointage->type == 'absence') {
             $pointage->update([
                 'type' => $type,
                 'heure_present' => $heure_actuelle->format('H:i:s'),
             ]);
         } else {
             // Sinon, créer un nouveau pointage d'arrivée
             $pointage = Pointage::create([
                 'user_id' => $user->id,
                 'type' => $type,
                 'date' => now()->toDateString(),
                 'heure_present' => $heure_actuelle->format('H:i:s'),
                 'created_by' => $vigile->id,
             ]);
         }

         return response()->json([
             'success' => true,
             'message' => 'Pointage d\'arrivée enregistré avec succès',
             'pointage' => $pointage,
             'user' => $user,
         ]);
     }


     public function pointageDepart(Request $request)
     {
// created_by user connect
$vigile = auth()->user();

// Vérifier si l'utilisateur est un formateur
if (!$vigile->hasRole('Vigile')) {
    return response()->json([
        'success' => false,
       'message' => 'Vous n\'êtes pas autorisé à accéder à cette section.',
    ], 403);
}

         // Validation des données d'entrée
         $validator = validator($request->all(), [
             'matricule' => ['required', 'exists:users,matricule'],
         ]);

         if ($validator->fails()) {
             return response()->json([
                 'success' => false,
                 'errors' => $validator->errors(),
             ], 422);
         }

         // Récupérer l'utilisateur
         $user = User::where('matricule', $request->matricule)->firstOrFail();
         if ($user->hasRole('Formateur')) {

         // Vérifier si un pointage d'arrivée existe pour aujourd'hui sans heure de départ
         $pointage = Pointage::where('user_id', $user->id)
                     ->where('date', now()->toDateString())
                     ->whereNotNull('heure_present')
                     ->first();

         // Vérifier si une heure de départ a déjà été enregistrée
         if ($pointage && $pointage->heure_depart !== null) {
             return response()->json([
                 'success' => false,
                 'message' => 'L\'utilisateur a déjà pointé son départ pour cette date.',
             ], 400);
         }

         if (!$pointage) {
             return response()->json([
                 'success' => false,
                 'message' => 'Aucun pointage d\'arrivée trouvé pour cette date.',
             ], 400);
         }

         // Enregistrer l'heure de départ
         $heure_actuelle = now();
         $pointage->update([
             'heure_depart' => $heure_actuelle->format('H:i:s'),
         ]);

         return response()->json([
             'success' => true,
             'message' => 'Heure de départ enregistrée avec succès.',
             'pointage' => $pointage,
         ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Pas autorisé à effectuer cette action..',
        ], 403);
     }

            public function afficherPointagesAujourdHuiTous(Request $request)
            {
                // Récupérer la date d'aujourd'hui
            $dateAujourdhui = now()->toDateString();

            // Récupérer les pointages pour aujourd'hui pour tous les utilisateurs
            $pointages = Pointage::where('date', $dateAujourdhui)
                ->with('user') // Charger les détails de l'utilisateur en même temps
                ->get();

            // Filtrer les utilisateurs pour obtenir uniquement les apprenants et formateurs
            $usersAvecPointage = $pointages->filter(function ($pointage) {
                return $pointage->user->hasRole(['Apprenant', 'Formateur']);
            });

            if ($usersAvecPointage->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun apprenant ou formateur n\'a pointé aujourd\'hui.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pointages des apprenants et formateurs récupérés avec succès.',
                'pointages' => $usersAvecPointage,
            ]);
        }

        // public function marquerAbsences()
        // {
        //     // Obtenir la date d'aujourd'hui
        //     $dateAujourdHui = now()->toDateString();

        //     // Récupérer les utilisateurs dont la promo est en cours
        //     $users = User::whereHas('promos', function ($query) {
        //         $query->where('date_debut', '<=', now())
        //               ->where('date_fin', '>=', now());
        //     })->get();

        //     // Parcourir les utilisateurs de la promotion en cours
        //     foreach ($users as $user) {
        //         // Vérifier si l'utilisateur a déjà pointé aujourd'hui
        //         $pointage = Pointage::where('user_id', $user->id)
        //             ->where('date', $dateAujourdHui)
        //             ->first();

        //         // Si aucun pointage trouvé, marquer comme absence
        //         if (!$pointage) {
        //             Pointage::create([
        //                 'user_id' => $user->id,
        //                 'type' => 'absence', // Assurez-vous que le type 'absence' est correct dans votre énumération
        //                 'date' => $dateAujourdHui,
        //                 'created_by' => auth()->id(), // Utilisateur qui enregistre l'absence
        //             ]);
        //         }
        //     }

        //     return response()->json([
        //         'success' => true,
        //         'message' => 'Absences marquées avec succès pour les utilisateurs de la promotion en cours.',
        //     ]);
        // }

        public function marquerAbsences()
        {
            // Obtenir la date d'aujourd'hui
            $dateAujourdHui = now()->toDateString();

            // Récupérer l'utilisateur formateur connecté
            $formateur = auth()->user();

            // Récupérer les utilisateurs dont la promo est en cours et dont le formateur est l'utilisateur connecté
            $users = User::whereHas('promos', function ($query) use ($formateur) {
                $query->where('date_debut', '<=', now())
                      ->where('date_fin', '>=', now())
                      ->where('formateur_id', $formateur->id); // Filtrer par formateur connecté
            })->get();

            // Parcourir les utilisateurs de la promotion en cours
            foreach ($users as $user) {
                // Vérifier si l'utilisateur a déjà pointé aujourd'hui
                $pointage = Pointage::where('user_id', $user->id)
                    ->where('date', $dateAujourdHui)
                    ->first();

                // Si aucun pointage trouvé, marquer comme absence
                if (!$pointage) {
                    Pointage::create([
                        'user_id' => $user->id,
                        'type' => 'absence', // Assurez-vous que le type 'absence' est correct dans votre énumération
                        'date' => $dateAujourdHui,
                        'created_by' => $formateur->id, // Utilisateur qui enregistre l'absence (formateur)
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Absences marquées avec succès pour les utilisateurs de la promotion en cours.',
            ]);
        }

        public function afficherPointagesAujourdHui(Request $request)
        {
            $vigile = auth()->user();

            // Récupérer la date d'aujourd'hui
            $dateAujourdhui = now()->toDateString();

            // Récupérer les pointages pour aujourd'hui pour le vigile connecté
            $pointages = Pointage::where('date', $dateAujourdhui)
                ->with('user') // Charger les détails de l'utilisateur en même temps
                ->where('created_by', $vigile->id)
                ->get();

            // Filtrer les utilisateurs pour obtenir uniquement les apprenants et formateurs ayant une promo
            $usersAvecPointage = $pointages->filter(function ($pointage) {
                $user = $pointage->user;
                return $user->hasRole(['Apprenant', 'Formateur']) && !empty($user->promos);
            });

            if ($usersAvecPointage->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun apprenant ou formateur de cette promotion n\'a pointé aujourd\'hui.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pointages des apprenants et formateurs récupérés avec succès.',
                'pointages' => $usersAvecPointage,
            ]);
        }

    public function afficherPointagesAujourdHuiVigile(Request $request)
    {
        // Récupérer la date d'aujourd'hui
    $dateAujourdhui = now()->toDateString();

    // Récupérer les pointages pour aujourd'hui pour tous les utilisateurs
    $pointages = Pointage::where('date', $dateAujourdhui)
        ->with('user') // Charger les détails de l'utilisateur en même temps
        ->get();

    // Filtrer les utilisateurs pour obtenir uniquement les apprenants et formateurs
    $usersAvecPointage = $pointages->filter(function ($pointage) {
        return $pointage->user->hasRole(['Apprenant', 'Formateur']);
        // return $pointage->user->hasRole(['Apprenant', 'Formateur']);
    });
    // user promo
    $usersAvecPointage = $pointages->filter(function ($pointage) {
        return $pointage->user->promos;
    });

    if ($usersAvecPointage->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'Aucun apprenant ou formateur de cette promotion n\'a pointé aujourd\'hui.',
        ], 404);
    }

    return response()->json([
       'success' => true,
       'message' => 'Pointages des apprenants et formateurs récupérés avec succès.',
        'pointages' => $usersAvecPointage,
    ]);

    if ($usersAvecPointage->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'Aucun apprenant ou formateur n\'a pointé aujourd\'hui.',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Pointages des apprenants et formateurs récupérés avec succès.',
        'pointages' => $usersAvecPointage,
    ]);
}
    public function afficherPointagesAujourdHuiParPromo(Request $request, $promoId)
    {
        // Récupérer la date d'aujourd'hui
        $dateAujourdhui = now()->toDateString();

        // Récupérer les pointages pour aujourd'hui pour les utilisateurs de la promo donnée
        $pointages = Pointage::where('date', $dateAujourdhui)
            ->whereHas('user', function($query) use ($promoId) {
                $query->where('promo_id', $promoId); // Filtrer par promotion
            })
            ->with('user') // Charger les détails de l'utilisateur en même temps
            ->get();

        // Vérification de l'existence de pointages pour la promo
        if ($pointages->isEmpty()) {
            Log::info('Pas de pointage trouvé pour la promo ID: ' . $promoId . ' à la date: ' . $dateAujourdhui);

            // Vérifiez si des utilisateurs existent dans cette promo
            $utilisateursPromo = User::where('promo_id', $promoId)->count();
            Log::info('Nombre d\'utilisateurs dans la promo: ' . $utilisateursPromo);

            if ($utilisateursPromo == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun utilisateur dans cette promotion.',
                ], 404);
            }

            return response()->json([
                'success' => false,
                'message' => 'Aucun pointage pour cette promotion aujourd\'hui.',
            ], 404);
        }

        // Filtrer les utilisateurs pour obtenir uniquement les apprenants et formateurs
        $usersAvecPointage = $pointages->filter(function ($pointage) {
            return $pointage->user->hasRole(['Apprenant', 'Formateur']);
        });

        if ($usersAvecPointage->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun apprenant ou formateur de cette promotion n\'a pointé aujourd\'hui.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pointages des apprenants et formateurs récupérés avec succès.',
            'pointages' => $usersAvecPointage,
        ]);
    }

public function afficherPointagesPromoAujourdHui(Request $request)
{
    // Validation des données d'entrée
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




public function MesPointagesdesmonPromo(Request $request, $promoId)
{
  $user = auth()->user(); // Récupérer l'utilisateur connecté
    $promoId = $request->input('promo_id');
    $date = $request->input('date');
    $mois = $request->input('mois');
    $annee = $request->input('annee');
    $semaine = $request->input('semaine');

    // Vérification des paramètres requis
    if (!$mois || !$annee) {
        return response()->json([
            'success' => false,
            'message' => 'Les paramètres mois et année sont requis.',
        ], 400);
    }

    // Vérifiez si l'utilisateur a accès à la promotion
    $hasAccess = $user->promos()->where('promos.id', $promoId)->exists();

    if (!$hasAccess) {
        return response()->json([
            'success' => false,
            'message' => 'Vous n\'avez pas accès à cette promotion.',
        ], 403);
    }


    // Calculer les dates de début et de fin
    if ($semaine) {
        $date_debut = Carbon::now()->setISODate($annee, $semaine)->startOfWeek();
        $date_fin = Carbon::now()->setISODate($annee, $semaine)->endOfWeek();
    } elseif ($date) {
        $date_debut = Carbon::parse($date)->startOfDay();
        $date_fin = Carbon::parse($date)->endOfDay();
    } else {
        $date_debut = Carbon::createFromDate($annee, $mois, 1)->startOfMonth();
        $date_fin = Carbon::createFromDate($annee, $mois, 1)->endOfMonth();
    }

    // Récupérer les pointages pour la période sélectionnée
    $pointages = Pointage::where('user_id', $user->id)
        ->where('promo_id', $promoId)
        ->whereBetween('date', [$date_debut, $date_fin])
        ->get();

    // Vérification des résultats
    if ($pointages->isEmpty()) {
        return response()->json([
            'success' => true,
            'message' => 'Aucun pointage trouvé pour cette période.',
            'pointages' => [],
        ], 200);
    }

    return response()->json([
        'success' => true,
        'message' => 'Pointages récupérés avec succès.',
        'pointages' => $pointages,
    ]);
}




// public function afficherPointagesPromo(Request $request)
//     {
//         // Récupérer l'ID de la promotion depuis les paramètres de la requête GET
//         $promotionId = $request->query('promo_id');

//         // Validation des données d'entrée
//         $validator = validator(['promo_id' => $promotionId], [
//             'promo_id' => ['required', 'exists:promos,id'], // Vérifie que la promo existe
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'success' => false,
//                 'errors' => $validator->errors(),
//             ], 422);
//         }

//         // Récupérer la promotion avec sa date de début
//         $promotion = Promo::find($promotionId);

//         // Si la promotion n'est pas trouvée (par sécurité)
//         if (!$promotion) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'La promotion n\'existe pas.',
//             ], 404);
//         }

//         // Récupérer les utilisateurs (apprenants et formateurs) qui appartiennent à la promotion
//         $users = User::whereHas('promos', function ($query) use ($promotionId) {
//             $query->where('promos.id', $promotionId);
//         })
//         ->whereHas('roles', function ($query) {
//             $query->whereIn('name', ['Apprenant', 'Formateur']);
//         })
//         ->pluck('id'); // Récupérer uniquement les IDs des utilisateurs

//         // Récupérer les pointages depuis la date de début de la promotion jusqu'à aujourd'hui
//         $pointages = Pointage::whereIn('user_id', $users)
//             ->whereBetween('date', [$promotion->date_debut, now()->toDateString()]) // Filtrer entre la date de début et aujourd'hui
//             ->with('user') // Charger les informations de l'utilisateur
//             ->get();

//         // Vérifier si des pointages ont été trouvés
//         if ($pointages->isEmpty()) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Aucun pointage trouvé pour cette promotion depuis sa date de début.',
//             ], 404);
//         }

//         return response()->json([
//             'success' => true,
//             'message' => 'Pointages des apprenants et formateurs récupérés avec succès.',
//             'pointages' => $pointages,
//         ]);
//     }

public function MesPointages()
{
    // Récupérer l'utilisateur connecté
    $user = auth()->user();

    // Récupérer les promotions auxquelles l'utilisateur est rattaché (en tant qu'apprenant ou formateur)
    $promo = Promo::whereHas('apprenants', function($query) use ($user) {
        $query->where('users.id', $user->id);
    })
    ->orWhere('formateur_id', $user->id)
    ->first();

    // Vérifier si l'utilisateur est rattaché à une promotion
    if (!$promo) {
        return response()->json([
            'success' => false,
            'message' => 'Vous n\'êtes rattaché à aucune promotion.',
        ], 404);
    }

    // Récupérer les pointages de l'utilisateur connecté depuis le début de la promotion
    $pointages = Pointage::where('user_id', $user->id)
        ->whereBetween('date', [$promo->date_debut, now()->toDateString()]) // Entre la date de début de la promo et aujourd'hui
        ->get();

    // Vérifier si des pointages existent
    if ($pointages->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'Aucun pointage trouvé depuis le début de la promotion.',
        ], 404);
    }

    // Retourner les pointages trouvés
    return response()->json([
        'success' => true,
        'message' => 'Vos pointages depuis le début de la promotion ont été récupérés avec succès.',
        'pointages' => $pointages,
    ]);
}

// pointageAujourdhui
    // public function pointageAujourdhui()
    // {
    //     // Récupérer l'utilisateur connecté
    //     $user = auth()->user();

    //     // Récupérer la date d'aujourd'hui
    //     $dateAujourdhui = now()->toDateString();

    //     // Récupérer les pointages de l'utilisateur connecté pour aujourd'hui
    //     $pointages = Pointage::where('user_id', $user->id)
    //         ->where('date', $dateAujourdhui)
    //         ->get();

    //     // Vérifier si des pointages existent
    //     if ($pointages->isEmpty()) {
    //         return response()->json([
    //            'success' => false,
    //            'message' => 'Aucun pointage trouvé pour aujourd\'hui.',
    //         ], 404);
    //     }
    //     return response()->json([
    //         'success' => true,
    //        'message' => 'Votre pointage pour aujourd\'hui a été récupéré avec succès.',
    //         'pointages' => $pointages,
    //     ]);

    // }
    public function pointageAujourdhui()
    {
        // Récupérer l'utilisateur connecté
        $user = auth()->user();
        $date = $request->input('date');

        // Récupérer la date d'aujourd'hui
        // $date = now()->toDateString();

        // Récupérer les pointages de l'utilisateur connecté pour aujourd'hui
        $pointages = Pointage::where('user_id', $user->id)
            ->where('date', $date)
            ->get();

        // Vérifier si des pointages existent
        if ($pointages->isEmpty()) {
            return response()->json([
               'success' => false,
               'message' => 'Aucun pointage trouvé pour aujourd\'hui.',
            ], 404);
        }
        return response()->json([
            'success' => true,
           'message' => 'Votre pointage pour aujourd\'hui a été récupéré avec succès.',
            'pointages' => $pointages,
        ]);

    }
    // pointageParSemaine
//     public function pointageParSemaine()
//     {
//        $validator = validator($request->all(), [
//         'promo_id' => ['required', 'exists:promos,id'], // Vérifie que la promo existe
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'success' => false,
//             'errors' => $validator->errors(),
//         ], 422);
//     }

//     // Récupérer la date d'aujourd'hui
//     $date = $request->input('date');

//     // Récupérer l'ID de la promotion depuis la requête
//     $promotionId = $request->input('promo_id');

//     // Récupérer les utilisateurs (apprenants et formateurs) qui appartiennent à la promotion
//     $users = User::whereHas('promos', function($query) use ($promotionId) {
//         $query->where('promos.id', $promotionId);
//     })
//     ->whereHas('roles', function($query) {
//         $query->whereIn('name', ['Apprenant', 'Formateur']);
//     })
//     ->pluck('id'); // Récupère uniquement les IDs des utilisateurs

//     // Récupérer les pointages des utilisateurs pour aujourd'hui
//     $pointages = Pointage::whereIn('user_id', $users)
//         ->where('date', $date)
//         ->with('user') // Charger les informations de l'utilisateur en même temps
//         ->get();

//     // Vérifier si des pointages ont été trouvés
//     if ($pointages->isEmpty()) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Aucun apprenant ou formateur n\'a pointé aujourd\'hui dans cette promotion.',
//         ], 404);
//     }

//     return response()->json([
//         'success' => true,
//         'message' => 'Pointages des apprenants et formateurs récupérés avec succès.',
//         'pointages' => $pointages,
//     ]);
// }
public function pointageParSemaine(Request $request)
{
    // Validation et récupération des paramètres comme précédemment
  // Valider l'entrée pour s'assurer que promo_id est fourni et valide
  $validator = validator($request->all(), [
    'promo_id' => ['required', 'exists:promos,id'], // Vérifie que la promo existe
    'date' => ['required', 'date'], // Vérifie que la date est valide
]);

if ($validator->fails()) {
    return response()->json([
        'success' => false,
        'errors' => $validator->errors(),
    ], 422);
}

// Récupérer la date donnée depuis la requête
$date = $request->input('date');
$promoId = $request->input('promo_id');

    // Calculer le début et la fin de la semaine
    $startOfWeek = Carbon::parse($date)->startOfWeek();
    $endOfWeek = Carbon::parse($date)->endOfWeek();

    // Récupérer les utilisateurs concernés
    $users = User::whereHas('promos', function($query) use ($promoId) {
        $query->where('promos.id', $promoId);
    })
    ->whereHas('roles', function($query) {
        $query->whereIn('name', ['Apprenant', 'Formateur']);
    })
    ->pluck('id');

    // Récupérer les pointages de la semaine
    $pointages = Pointage::whereIn('user_id', $users)
        ->whereBetween('date', [$startOfWeek, $endOfWeek])
        ->with('user')
        ->get();

    // Structurer les données pour renvoyer par jour
    $result = [];
    foreach ($pointages as $pointage) {
        $jour = Carbon::parse($pointage->date)->format('l'); // Obtenir le jour de la semaine
        $result[$pointage->user_id]['user'] = $pointage->user;
        $result[$pointage->user_id]['date'][$jour] = $pointage->heure_present; // Stocker l'heure présente
    }

    // Renvoyer les données structurées
    return response()->json([
        'success' => true,
        'pointages' => array_values($result), // Pour obtenir un tableau indexé
    ]);
}



public function pointageParSemaineUnPromo(Request $request, $promo_id)
{
    $startOfWeek = Carbon::parse($request->input('start_date'))->startOfWeek();
    $endOfWeek = Carbon::parse($request->input('end_date'))->endOfWeek();

    // Récupérer les apprenants associés à la promo via la table de liaison
    $apprenants = User::whereHas('apprenantPromo', function ($query) use ($promo_id) {
        $query->where('promo_id', $promo_id);
    })->get();

    // Récupérer les pointages de la semaine pour ces apprenants
    $pointages = Pointage::whereIn('user_id', $apprenants->pluck('id'))
        ->whereBetween('date', [$startOfWeek, $endOfWeek])
        ->get();

    // Structurer les résultats pour chaque apprenant et chaque jour de la semaine
    $result = [];

    foreach ($apprenants as $apprenant) {
        $apprenantPointages = [];

        // Pour chaque jour de la semaine
        for ($date = $startOfWeek; $date->lte($endOfWeek); $date->addDay()) {
            // Trouver le pointage de l'apprenant pour ce jour
            $pointageDuJour = $pointages->where('user_id', $apprenant->id)->where('date', $date->toDateString())->first();

            // Ajouter le statut du pointage (présent, retard, absence) pour chaque jour
            if ($pointageDuJour) {
                $apprenantPointages[$date->toDateString()] = $pointageDuJour->type;
            } else {
                $apprenantPointages[$date->toDateString()] = 'absence'; // Si aucun pointage, considérer comme absence
            }
        }

        // Ajouter les données de l'apprenant
        $result[] = [
            'apprenant' => $apprenant->prenom . ' ' . $apprenant->nom,
            'pointages' => $apprenantPointages
        ];
    }

    return response()->json([
        'pointages_apprenants' => $result,
        'start_of_week' => $startOfWeek->toDateString(),
        'end_of_week' => $endOfWeek->toDateString(),
    ]);
}


}


    //     public function pointageParSemaine()
    //     {
    //         // Récupérer l'utilisateur connecté
    //         $user = auth()->user();

    //         // Récupérer la date d'aujourd'hui
    //         $dateAujourdhui = now()->toDateString();

    //         // Récupérer le premier jour de la semaine pour la date d'aujourd'hui
    //         $premierJourSemaine = now()->startOfWeek()->toDateString();

    //         // Récupérer les pointages de l'utilisateur connecté pour la semaine
    //         $pointages = Pointage::where('user_id', $user->id)
    //             ->whereBetween('date', [$premierJourSemaine, $dateAujourdhui])
    //             ->get();

    //         // Vérifier si des pointages existent
    //     // Vérifier si des pointages existent
    // if ($pointages->isEmpty()) {
    //     return response()->json([
    //        'success' => true,
    //        'message' => 'Aucun pointage trouvé pour cette semaine.',
    //        'pointages' => [],
    //     ], 200);
    // }

    //         }

