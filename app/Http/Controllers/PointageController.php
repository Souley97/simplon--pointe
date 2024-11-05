<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Promo;
use App\Models\Pointage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf; // Assurez-vous d'installer la bibliothèque dompdf si ce n'est pas déjà fait
use Illuminate\Support\Facades\Validator;


class PointageController extends Controller
{
    /**
     * Display a listing of the resource.
     */


     public function pointageArrivee(Request $request)
     {
         // Utilisateur connecté
        //  $vigile = auth()->user();
     
        //  // Vérifier si l'utilisateur est un vigile
        //  if (!$vigile->hasRole('Vigile')) {
        //      return response()->json([
        //          'success' => false,
        //          'message' => 'Vous n\'êtes pas autorisé à accéder à cette section.',
        //      ], 403);
        //  }
     
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
     
         // Déterminer si l'utilisateur est un apprenant ou un formateur
         if ($user->hasRole('Formateur')) {
             // Récupérer la promotion active pour ce formateur
             $promo = Promo::where('formateur_id', $user->id)
                           ->where('statut', 'encours')
                           ->first();
         } else {
             // Récupérer la promotion active pour l'apprenant
             $promo = $user->promos()->where('statut', 'encours')->first();
         }
     
         if (!$promo || !$promo->horaire) {
             return response()->json([
                 'success' => false,
                 'message' => 'Aucune promotion active trouvée pour cet utilisateur ou horaire manquant.',
             ], 404);
         }
     
         // Déterminer l'heure actuelle et l'horaire de la promotion
         $heure_actuelle = now();
         $heure_limite = Carbon::parse($promo->horaire);
     
         // Déterminer le type de pointage : 'retard' ou 'present'
         $type = $heure_actuelle->greaterThan($heure_limite) ? 'retard' : 'present';
     
         // Si l'utilisateur a été marqué comme absent, mettre à jour le pointage existant
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
                 'created_by' => 32,
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

    public function pointageAujourdhui( Request $request)
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
 
public function pointageParSemaine(Request $request)
{
       // Validation de la requête
       $validator = validator($request->all(), [
        'promo_id' => ['required', 'exists:promos,id'],
        'start_date' => ['required', 'date'],
        'end_date' => ['required', 'date', 'after_or_equal:start_date'],
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    // Récupérer les paramètres
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
    $promoId = $request->input('promo_id');

    // Récupérer les utilisateurs et leurs pointages
    $users = User::whereHas('promos', function ($query) use ($promoId) {
        $query->where('promos.id', $promoId);
    })
    ->whereHas('roles', function ($query) {
        $query->whereIn('name', ['Apprenant', 'Formateur']);
    })
    ->get();

    // Récupérer les pointages de la période sélectionnée
    $pointages = Pointage::whereIn('user_id', $users->pluck('id'))
        ->whereBetween('date', [$startDate, $endDate])
        ->get()
        ->groupBy('user_id');

    // Structurer les données pour chaque utilisateur
    $result = [];
    foreach ($users as $user) {
        $userData = [
            'user' => $user,
            'dates' => [],
        ];

        foreach ($pointages[$user->id] ?? [] as $pointage) {
            $jour = Carbon::parse($pointage->date)->format('Y-m-d');
            $heure = $pointage->heure_present;
            $status = 'Absent';

            // Définir l'heure de pointage ou 'Retard' si l'heure est après 09:00
            if ($heure) {
                $status = Carbon::parse($heure)->gt('09:00:00') ? 'Retard' : 'Présent';
            }

            $userData['dates'][$jour] = $status;
        }

        // Compléter les jours manquants avec 'Absent'
        $period = Carbon::parse($startDate)->toPeriod($endDate);
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            if (!isset($userData['dates'][$formattedDate])) {
                $userData['dates'][$formattedDate] = 'Absent';
            }
        }

        $result[] = $userData;
    }

    // Vérifier si un export PDF est demandé
    if ($request->has('export') && $request->input('export') === 'pdf') {
        $pdf = Pdf::loadView('exports.pointages', ['pointages' => $result, 'start_date' => $startDate, 'end_date' => $endDate]);
        return $pdf->download('pointages.pdf');
    }

    // Renvoyer la réponse structurée en JSON
    return response()->json([
        'success' => true,
        'pointages' => $result,
    ]);
}
public function pointageParPeriode(Request $request)
{
    // Validation de la requête
    $validator = Validator::make($request->all(), [
        'promo_id' => ['required', 'exists:promos,id'],
        'start_date' => ['required', 'date'],
        'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        'export' => ['nullable', 'in:pdf'], // Ajout de la validation pour l'export PDF
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    // Récupérer les paramètres
    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
    $promoId = $request->input('promo_id');

    // Récupérer les utilisateurs et leurs pointages
    $users = User::whereHas('promos', function ($query) use ($promoId) {
        $query->where('promos.id', $promoId);
    })
    ->whereHas('roles', function ($query) {
        $query->whereIn('name', ['Apprenant', 'Formateur']);
    })
    ->get();

    // Récupérer les pointages de la période sélectionnée
    $pointages = Pointage::whereIn('user_id', $users->pluck('id'))
        ->whereBetween('date', [$startDate, $endDate])
        ->get()
        ->groupBy('user_id');

    // Dates de la période
    $datesRange = [];
    for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
        $datesRange[] = $date->format('Y-m-d');
    }

    // Structurer les données pour chaque utilisateur
    $result = [];
    foreach ($users as $user) {
        $userData = [
            'user' => $user,
            'dates' => [],
              'absences' => 0, // Add absences count
            'tardies' => 0,  // Add tardies count
        ];

        // Vérifier les jours de pointage pour l'utilisateur
        foreach ($datesRange as $jour) {
            // Vérifier si l'utilisateur a pointé ce jour-là
            if (isset($pointages[$user->id]) && $pointages[$user->id]->where('date', $jour)->isNotEmpty()) {
                $pointage = $pointages[$user->id]->where('date', $jour)->first();
                $heure = $pointage->heure_present;
                $status = Carbon::parse($heure)->gt('05:00') ? 'Retard' : 'Présent';
                $userData['dates'][$jour] = $status;
                if ($status === 'Retard') {
                    $userData['tardies']++;
                }
            }
        }

        // Ne conserver que les dates où il y a eu un pointage
        if (!empty($userData['dates'])) {
            $result[] = $userData;
        }
    }

    // Vérifier si un export PDF est demandé
    if ($request->has('export') && $request->input('export') === 'pdf') {
        $pdf = \PDF::loadView('exports.pointages', [
            'pointages' => $result,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]);
        return $pdf->download('pointages.pdf');
    }

    // Renvoyer la réponse structurée en JSON
    return response()->json([
        'success' => true,
        'pointages' => $result,
    ]);
}

// public function pointageParPeriode(Request $request)
// {
//     // Validation de la requête
//     $validator = Validator::make($request->all(), [
//         'promo_id' => ['required', 'exists:promos,id'],
//         'start_date' => ['required', 'date'],
//         'end_date' => ['required', 'date', 'after_or_equal:start_date'],
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'success' => false,
//             'errors' => $validator->errors(),
//         ], 422);
//     }

//     // Récupérer les paramètres
//     $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
//     $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
//     $promoId = $request->input('promo_id');

//     // Récupérer les utilisateurs et leurs pointages
//     $users = User::whereHas('promos', function ($query) use ($promoId) {
//         $query->where('promos.id', $promoId);
//     })
//     ->whereHas('roles', function ($query) {
//         $query->whereIn('name', ['Apprenant', 'Formateur']);
//     })
//     ->get();

//     // Récupérer les pointages de la période sélectionnée
//     $pointages = Pointage::whereIn('user_id', $users->pluck('id'))
//         ->whereBetween('date', [$startDate, $endDate])
//         ->get()
//         ->groupBy('user_id');

//     // Dates de la période
//     $datesRange = [];
//     for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
//         $datesRange[] = $date->format('Y-m-d');
//     }

//     // Structurer les données pour chaque utilisateur
//     $result = [];
//     foreach ($users as $user) {
//         $userData = [
//             'user' => $user,
//             'dates' => [],
//             'absences' => 0, // Add absences count
//             'tardies' => 0,  // Add tardies count
//         ];

//         // Vérifier les jours de pointage pour l'utilisateur
//         foreach ($datesRange as $jour) {
//             // Vérifier si l'utilisateur a pointé ce jour-là
//             if (isset($pointages[$user->id]) && $pointages[$user->id]->where('date', $jour)->isNotEmpty()) {
//                 $pointage = $pointages[$user->id]->where('date', $jour)->first();
//                 $heure = $pointage->heure_present;
//                 $status = Carbon::parse($heure)->gt('18:00') ? 'Retard' : 'Présent';
//                 $userData['dates'][$jour] = $status;

//                 if ($status === 'Retard') {
//                     $userData['tardies']++;
//                 }
//             } else {
//                 // If the user didn't point, mark as absent
//                 $userData['dates'][$jour] = 'Absent';
//                 $userData['absences']++;
//             }
//         }

//         // Ne conserver que les dates où il y a eu un pointage
//         if (!empty($userData['dates'])) {
//             $result[] = $userData;
//         }
//     }

//     // Renvoyer la réponse structurée en JSON
//     return response()->json([
//         'success' => true,
//         'pointages' => $result,
//     ]);
// }


public function pointageParSemaineUnPromo(Request $request, $promo_id)
{
    $startOfWeek = Carbon::parse($request->input('start_date'))->startOfWeek();
    $endOfWeek = Carbon::parse($request->input('end_date'))->endOfWeek();

    // Récupérer les apprenants associés à la promo via la table de liaison
    $apprenants = User::whereHas('promos', function ($query) use ($promo_id) {
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
