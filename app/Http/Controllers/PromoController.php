<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Promo;
use App\Http\Requests\StorePromoRequest;
use App\Http\Requests\UpdatePromoRequest;

class PromoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // liste des promos
        $promos = Promo::all();
        // return json
        return response()->json($promos);


    }
    // Mes promos formateure connecte
    public function mesPromos()
    {
        // Récupérer l'utilisateur connecté
        $user = auth()->user();

        // Vérifier si l'utilisateur est un formateur ou un ChefDeProjet
        if (!$user->hasRole(['Formateur', 'ChefDeProjet'])) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à accéder à cette section.',
            ], 403);
        }

        // Récupérer les promotions en fonction du rôle de l'utilisateur
        $promos = Promo::with('formateur') // Inclure la relation avec le formateur
            ->where('formateur_id', $user->id)
            ->orWhere('chef_projet_id', $user->id)
            ->get();

        // Vérifier si des promos existent pour l'utilisateur
        if ($promos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune promotion n\'est assignée à vous.',
            ], 404);
        }

        // Retourner la liste des promotions sous forme de JSON
        return response()->json([
            'success' => true,
            'message' => 'Promotions récupérées avec succès.',
            'promos' => $promos,
        ]);
    }


    public function mesPromosTermine(){
        // Récupérer l'utilisateur connecté
        $user = auth()->user();

        // Vérifier si l'utilisateur est un formateur ou un ChefDeProjet
        if (!$user->hasRole(['Formateur', 'ChefDeProjet'])) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à accéder à cette section.',
            ], 403);
        }

        // Récupérer les promotions terminées si l'utilisateur est formateur ou ChefDeProjet
        $promos = Promo::where('statut', 'termine')
            ->where('formateur_id', $user->id)
            ->orWhere('chef_projet_id', $user->id)
            ->get();

        // Vérifier si des promos existent pour l'utilisateur
        if ($promos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune promotion n\'est assignée à vous.',
            ], 404);
        }
        // Retourner la liste des promotions sous forme de JSON
        return response()->json([
            'success' => true,
            'message' => 'Promotions récupérées avec succès.',
            'promos' => $promos,
        ]);

    }


    public function mesPromosEncours() {
        $user = auth()->user();

        if (!$user->hasRole(['Formateur', 'ChefDeProjet'])) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à accéder à cette section.',
            ], 403);
        }

        // Récupérer les promotions en cours pour l'utilisateur
        $promos = Promo::where('statut', 'encours')
            ->where(function($query) use ($user) {
                $query->where('formateur_id', $user->id)
                      ->orWhere('chef_projet_id', $user->id);
            })
            ->get();

        // Mettre à jour le statut des promotions si la date de fin est passée
        foreach ($promos as $promo) {
            if (Carbon::parse($promo->date_fin)->lt(Carbon::now())) {
                $promo->statut = 'termine';
                $promo->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Promotions en cours récupérées avec succès.',
            'promos' => $promos,
        ]);
    }





    //

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
    public function store(StorePromoRequest $request)
    {
        // Vérification si l'utilisateur est un formateur
        if (!auth()->user()->hasRole('Formateur')) {
            return response()->json([
               'success' => false,
               'message' => 'Vous n\'êtes pas autorisé à effectuer cette action.',
            ], 403);
        }

        // Création de la promotion
        $promo = Promo::create([
            'nom' => $request->input('nom'),
            'date_debut' => $request->input('date_debut'),
            'date_fin' => $request->input('date_fin'),
            'statut' => 'encours',
            'fabrique_id' => $request->input('fabrique_id'),
            'formateur_id' => auth()->user()->id, // Le formateur connecté est assigné
            'chef_projet_id' => $request->input('chef_projet_id'),
            'formation_id' => $request->input('formation_id'),
        ]);

        // Retourner la promotion nouvellement créée
        return response()->json([
           'success' => true,
           'message' => 'Promotion créée avec succès.',
           'promo' => $promo,
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Promo $promo)
    {
        // Récupérer les apprenants associés à cette promotion
        $apprenants = $promo->apprenants()->get();

        // Retourner les détails de la promotion ainsi que les apprenants associés
        return response()->json([
            'success' => true,
            'message' => 'Détails de la promotion récupérés avec succès.',
            'promo' => $promo,
            'apprenants' => $apprenants,
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Promo $promo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */

//  formateur auth voir pointages de ses promos :
    public function update(UpdatePromoRequest $request, Promo $promo)
{
    // Vérification si l'utilisateur est un formateur
    if (!auth()->user()->hasRole('Formateur')) {
        return response()->json([
           'success' => false,
           'message' => 'Vous n\'êtes pas autorisé à effectuer cette action.',
        ], 403);
    }

    // Vérifier si la promotion appartient au formateur connecté
    if ($promo->formateur_id !== auth()->user()->id) {
        return response()->json([
           'success' => false,
           'message' => 'Vous n\'êtes pas habilité à modifier cette promotion.',
        ], 403);
    }

    // Mettre à jour la promotion
    $promo->update([
        'nom' => $request->input('nom'),
        'date_debut' => $request->input('date_debut'),
        'date_fin' => $request->input('date_fin'),
        'statut' => $request->input('statut', 'encours'), // Si le statut peut être mis à jour
        'fabrique_id' => $request->input('fabrique_id'),
        'chef_projet_id' => $request->input('chef_projet_id'),
        'formation_id' => $request->input('formation_id'),
    ]);

    // Retourner la promotion mise à jour
    return response()->json([
       'success' => true,
       'message' => 'Promotion mise à jour avec succès.',
       'promo' => $promo,
    ]);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Promo $promo)
    {
        //
    }
}
