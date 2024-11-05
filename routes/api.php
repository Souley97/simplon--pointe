<?php

use Illuminate\Http\Request;
use App\Mail\ApprenantInscritMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\FabriqueController;
use App\Http\Controllers\PointageController;
use App\Http\Controllers\ApprenantController;
use App\Http\Controllers\FormateurController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\JustificatifController;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


// Routes pour authentification et déconnexion
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/qr/{matricule}', [QRCodeController::class, 'showQr']);

Route::middleware('auth:api')->group(function () {

    // Routes pour inscription d'apprenants , formateurs, ChefDeProjers, Vigiles
    Route::post('/formateur/inscrire', [AuthController::class, 'inscrireFormateur']);
    Route::post('/chef-de-projet/inscrire', [AuthController::class, 'inscrireChefDeProjet']);
    Route::post('/vigile/inscrire', [AuthController::class, 'inscrireVigile']);
    Route::post('/apprenants/import', [ApprenantController::class, 'inscrireApprenantsExcel']);
    Route::post('/apprenant/inscrire', [ApprenantController::class, 'inscrireApprenant']);



    // Routes pour promos
    Route::get('/promos/formateur', [PromoController::class, 'mesPromos']);
    Route::get('/promos/encours', [PromoController::class, 'mesPromosEncours']);
    Route::get('/promos/terminer', [PromoController::class, 'mesPromosTermine']);

    Route::get('/promos', [PromoController::class, 'index']);
    Route::post('/promos', [PromoController::class, 'store']);
    Route::put('/promos', [PromoController::class, 'update']);


// Afficher les pointages d'une promotion aujourd'hui
    Route::get('/promos/{promo}/pointages-aujourdhui', [PointageController::class, 'MesPointagesdesmonPromo']);


    Route   ::get('/mes-pointages', [PromoController::class, 'mesPointages']);

    // promo encours
    Route::get('/admin/promos/encours', [PromoController::class, 'promosEncours']);
    Route::get('/admin/promos/terminees', [PromoController::class, 'promosTerminees']);


    Route::get('/pointages/moi/apprenant', [ApprenantController::class, 'MesPointages'])->name('pointage.moi');


    Route::post('/update/information', [UserController::class, 'updateInformation']);

    // Route::middleware('auth:api')->group(function () {
    // });
    Route::post('/marquer-absences', [PointageController::class, 'marquerAbsences']);

    Route::post('/pointage/arrivee', [PointageController::class, 'pointageArrivee']);
    Route::post('/pointage/depart', [PointageController::class, 'pointageDepart']);

 // Routes pour pointages
    Route::get('/pointages/all', [PointageController::class, 'afficherPointagesAujourdHui'])->name('pointage');
    Route::get('/pointages/promo/all', [PointageController::class, 'afficherPointagesPromoAujourdHui']);
    Route::get('/pointages/promo/aujourhui', [PointageController::class, 'afficherPointagesPromoAujourdHui']);
    Route::get('/pointages/promo', [FormateurController::class, 'afficherPointagesPromo']);
    Route::get('/pointages/promos', [FormateurController::class, 'afficherPointagesPromos']);
    Route::get('/pointages/moi', [PointageController::class, 'MesPointages'])->name('pointage/moi');
    Route::get('/promo/{promoId}/pointages-aujourd-hui', [PointageController::class, 'afficherPointagesAujourdHuiParPromo']);
    // pointagePar semaine
    Route::get('/pointages/aujourdhui', [PointageController::class, 'afficherPointagesAujourdHui'])->name('pointage/user');
    Route::post('/pointages/semaines', [PointageController::class, 'pointageParSemaine']);
    Route::POST ('/promo/{promo_id}/pointages-semaine', [PointageController::class, 'pointageParSemaineUnPromo']);
    Route::POST('/promos/static', [FormateurController::class, 'staticPromo'])->name('pointage/static');
    Route::post('/pointages/periode', [PointageController::class, 'pointageParPeriode']);

 // Routes pour justifier

    Route::post('/justifier-absence/{absence}', [JustificatifController::class, 'justifierAbsence']);
    Route::get('/justifier-absence/{pointageId}', [JustificatifController::class, 'VoirJustifierAbsence']);

     // Routes pour conges
    Route::post('/conges', [CongeController::class, 'store']);
    Route::get('/conges', [CongeController::class, 'index']);
    Route::PATCH('/conges/{id}/status', [CongeController::class, 'updateStatus']);
    Route::DELETE('/conges/{id}', [CongeController::class, 'destroy']);
    Route::get('/mes/conges', [CongeController::class, 'myConge']);

     // Routes pour formations
    Route::post('/formations', [FormationController::class, 'store']);
    Route::get('/formations/{formation}', [FormationController::class, 'show']);
    Route::post('/formations/{formation}', [FormationController::class, 'update']);
    Route::delete('/formations/{formation}', [FormationController::class, 'destroy']);
    Route::get('/formations/{id}/promos', [FormationController::class, 'promos']);

    Route::get('/formations', [FormationController::class, 'index']);
    Route::get('/fabriques', [FabriqueController::class, 'index']);
    
    Route::get('/chefs-projet', [UserController::class, 'chefsProjet']);
        
     // Routes pour fabriques

    Route::post('/fabriques', [FabriqueController::class, 'store']);
    Route::get('/fabriques/{fabrique}', [FabriqueController::class, 'show']);
    Route::post('/fabriques/{fabrique}', [FabriqueController::class, 'update']);
    Route::delete('/fabriques/{fabrique}', [FabriqueController::class, 'destroy']);
    Route::get('/fabriques/{id}/promos', [FabriqueController::class, 'promos']);



    Route::middleware('auth:api')->group(function () {

    // formateur
    Route::get('/formateurs', [FormateurController::class, 'ListeFormateurs']);
    Route::get('formateurs/promotions', [FormateurController::class, 'getPromotions']);
    Route::get('/personnels/listes', [UserController::class, 'personnelListe']);

// Route::post('/formateurs', [FormateurController::class, 'store']);
});

// routes/api.php
Route::get('/user/role', function (Request $request) {
    $user = $request->user();

    if ($user->hasRole('Formateur')) {
        return response()->json(['role' => 'Formateur']);
    } elseif ($user->hasRole('Apprenant')) {
        return response()->json(['role' => 'Apprenant']);
    } elseif ($user->hasRole('Vigile')) {
        return response()->json(['role' => 'Vigile']);
    } elseif ($user->hasRole('ChefDeProjet')) {
        return response()->json(['role' => 'ChefDeProjet']);
    } elseif ($user->hasRole('Administrateur')) {
        return response()->json(['role' => 'Administrateur']);
    }

    return response()->json(['role' => 'Unknown'], 404);


    // routes/api.php


});
});
// routes/web.php

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return response()->json([
        'user' => $request->user(),
        'role' => $request->user()->roles->pluck('name') // Renvoie les noms des rôles
    ]);
});

