<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Promo;
use App\Models\Pointage;
use Illuminate\Http\Request;
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

         // Pour les formateurs : gérer l'heure de départ
         if ($user->hasRole('Formateur')) {
             // Rechercher un pointage d'arrivée déjà effectué pour la journée
             $pointageArrivee = Pointage::where('user_id', $user->id)
                             ->where('date', now()->toDateString())
                             ->whereNull('heur_depart') // Chercher uniquement les pointages sans départ
                             ->first();

             if ($pointageArrivee) {
                 // Enregistrer l'heure de départ
                 $pointageArrivee->update([
                     'heur_depart' => now()->format('H:i:s'),
                 ]);

                 return response()->json([
                     'success' => true,
                     'message' => 'Heure de départ enregistrée avec succès pour le formateur.',
                     'pointage' => $pointageArrivee,
                 ]);
             }
         }

         // Vérifier s'il y a déjà un pointage d'arrivée pour la journée
         $pointage = Pointage::where('user_id', $user->id)
                     ->where('date', now()->toDateString())
                     ->whereNotNull('heure_present')
                     ->first();

         if ($pointage && $user->hasRole(['Formateur', 'Apprenant'])) {
             return response()->json([
                 'success' => false,
                 'message' => 'L\'utilisateur a déjà pointé pour cette date.',
             ], 400);
         }

         // Déterminer l'heure actuelle et le type de pointage
         $heure_actuelle = now();
         $heure_limite = now()->setTime(9, 0);

         $type = $heure_actuelle->greaterThan($heure_limite) ? 'retard' : 'present';

         // Enregistrer le pointage d'arrivée
         $pointage = Pointage::create([
             'user_id' => $user->id,
             'type' => $type,
             'date' => now()->toDateString(),
             'heure_present' => $heure_actuelle->format('H:i:s'),
         ]);

         return response()->json([
             'success' => true,
             'message' => 'Pointage d\'arrivée enregistré avec succès',
             'pointage' => $pointage,
             'user' => $user,
         ]);
     }

     public function pointageDepart(Request $request)
     {
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

     public function afficherPointagesAujourdHui(Request $request)
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
public function afficherPointagesPromoAujourdHui(Request $request)
{
    // Validation des données d'entrée
    $validator = validator($request->all(), [
        'promo_id' => ['required', 'exists:promos,id'], // Vérifie que la promo existe
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    // Récupérer la date d'aujourd'hui
    $dateAujourdhui = now()->toDateString();

    // Récupérer l'ID de la promotion depuis la requête
    $promotionId = $request->input('promo_id');

    // Récupérer les utilisateurs (apprenants et formateurs) qui appartiennent à la promotion
    $users = User::whereHas('promos', function($query) use ($promotionId) {
        $query->where('promos.id', $promotionId);
    })
    ->whereHas('roles', function($query) {
        $query->whereIn('name', ['Apprenant', 'Formateur']);
    })
    ->pluck('id'); // Récupère uniquement les IDs des utilisateurs

    // Récupérer les pointages des utilisateurs pour aujourd'hui
    $pointages = Pointage::whereIn('user_id', $users)
        ->where('date', $dateAujourdhui)
        ->with('user') // Charger les informations de l'utilisateur en même temps
        ->get();

    // Vérifier si des pointages ont été trouvés
    if ($pointages->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'Aucun apprenant ou formateur n\'a pointé aujourd\'hui dans cette promotion.',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Pointages des apprenants et formateurs récupérés avec succès.',
        'pointages' => $pointages,
    ]);
}


public function afficherPointagesPromo(Request $request)
{
    // Validation des données d'entrée
    $validator = validator($request->all(), [
        'promo_id' => ['required', 'exists:promos,id'], // Vérifie que la promo existe
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    // Récupérer l'ID de la promotion depuis la requête
    $promotionId = $request->input('promo_id');

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
    $users = User::whereHas('promos', function($query) use ($promotionId) {
        $query->where('promos.id', $promotionId);
    })
    ->whereHas('roles', function($query) {
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePointageRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Pointage $pointage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pointage $pointage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePointageRequest $request, Pointage $pointage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pointage $pointage)
    {
        //
    }
}
