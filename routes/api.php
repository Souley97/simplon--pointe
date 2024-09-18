<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\FabriqueController;
use App\Http\Controllers\PointageController;
use App\Http\Controllers\ApprenantController;
use App\Http\Controllers\FormationController;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


// Routes pour authentification et déconnexion
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/qr/{matricule}', [QRCodeController::class, 'showQr']);

// Route::middleware('auth:api')->group(function () {

    // Routes pour inscription d'apprenants , formateurs, ChefDeProjers, Vigiles
    Route::post('/apprenant/inscrire', [ApprenantController::class, 'inscrireApprenant']);
    Route::post('/formateur/inscrire', [AuthController::class, 'inscrireFormateur']);
    Route::post('/chef-de-projet/inscrire', [AuthController::class, 'inscrireChefDeProjet']);
    Route::post('/vigile/inscrire', [AuthController::class, 'inscrireVigile']);
    Route::get('/pointages/semaines', [PointageController::class, 'pointageParSemaine'])->name('pointage/semaine');


    Route::get('/promos/formateur', [PromoController::class, 'mesPromos']);
    Route::get('/promos/encours', [PromoController::class, 'mesPromosEncours']);
    Route::get('/promos/terminer', [PromoController::class, 'mesPromosTermine']);

    Route::post('/promos', [PromoController::class, 'store']);
    Route::post('/promos', [PromoController::class, 'update']);

    Route::get('/pointages/moi/apprenant', [ApprenantController::class, 'MesPointages'])->name('pointage.moi');


    Route::post('/update/information', [UserController::class, 'updateInformation']);
    // });

    Route::post('/pointage/arrivee', [PointageController::class, 'pointageArrivee']);
    Route::post('/pointage/depart', [PointageController::class, 'pointageDepart']);
    Route::get('/pointages/all', [PointageController::class, 'afficherPointagesAujourdHui'])->name('pointage');
    Route::get('/pointages/promo/all', [PointageController::class, 'afficherPointagesPromoAujourdHui'])->name('pointage');
    Route::get('/pointages/promo', [PointageController::class, 'afficherPointagesPromo'])->name('pointagePromo');
    Route::get('/pointages/moi', [PointageController::class, 'MesPointages'])->name('pointage/moi');
    Route::get('/pointages/aujourdhui', [PointageController::class, 'afficherPointagesAujourdHui'])->name('pointage/user');



// Mise à jour d'une promotion par un formateur
Route::post('/promos/{promo}', [PromoController::class, 'update'])->middleware('auth:api');
Route::get('/promos/{promo}', [PromoController::class, 'show']);

// Afficher les pointages d'une promotion aujourd'hui
Route::get('/promos/{promo}/pointages-aujourdhui', [PromoController::class, 'afficherPointagesPromoAujourdHui'])->middleware('auth:api');

// Mes pointages (utilisateur connecté)
Route::get('/mes-pointages', [PromoController::class, 'mesPointages'])->middleware('auth:api');



Route::get('/formations', [FormationController::class, 'index']);
Route::post('/formations', [FormationController::class, 'store']);
Route::get('/formations/{formation}', [FormationController::class, 'show']);
Route::post('/formations/{formation}', [FormationController::class, 'update']);
Route::delete('/formations/{formation}', [FormationController::class, 'destroy']);
Route::get('/formations/{id}/promos', [FormationController::class, 'promos']);



Route::get('/fabriques', [FabriqueController::class, 'index']);
Route::post('/fabriques', [FabriqueController::class, 'store']);
Route::get('/fabriques/{fabrique}', [FabriqueController::class, 'show']);
Route::post('/fabriques/{fabrique}', [FabriqueController::class, 'update']);
Route::delete('/fabriques/{fabrique}', [FabriqueController::class, 'destroy']);
Route::get('/fabriques/{id}/promos', [FabriqueController::class, 'promos']);

Route::post('/apprenants/import', [ApprenantController::class, 'inscrireApprenantsExcel']);

Route::middleware('auth:api')->group(function () {

// routes/api.php
Route::get('/user/role', function (Request $request) {
    $user = $request->user();

    if ($user->hasRole('Formateur')) {
        return response()->json(['role' => 'Formateur']);
    } elseif ($user->hasRole('Apprenant')) {
        return response()->json(['role' => 'Apprenant']);
    } elseif ($user->hasRole('Vigile')) {
        return response()->json(['role' => 'Vigile']);
    } elseif ($user->hasRole('Chef-de-Projet')) {
        return response()->json(['role' => 'Chef de projet']);
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
