<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\PointageController;
use App\Http\Controllers\ApprenantController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Routes pour authentification et déconnexion
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/qr/{matricule}', [QRCodeController::class, 'showQr']);

Route::middleware('auth:api')->group(function () {

    // Routes pour inscription d'apprenants , formateurs, ChefDeProjers, Vigiles
    Route::post('/apprenant/inscrire', [ApprenantController::class, 'inscrireApprenant']);
    Route::post('/formateur/inscrire', [AuthController::class, 'inscrireFormateur']);
    Route::post('/chef-de-projet/inscrire', [AuthController::class, 'inscrireChefDeProjet']);
    Route::post('/vigile/inscrire', [AuthController::class, 'inscrireVigile']);

    Route::post('/update-information', [UserController::class, 'updateInformation']);
    });
    Route::post('/pointage/arrivee', [PointageController::class, 'pointageArrivee']);
    Route::post('/pointage/depart', [PointageController::class, 'pointageDepart']);
    Route::get('/pointages/all', [PointageController::class, 'afficherPointagesAujourdHui'])->name('pointage');
    Route::get('/pointages/promo/all', [PointageController::class, 'afficherPointagesPromoAujourdHui'])->name('pointage');
