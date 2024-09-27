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
use App\Http\Controllers\FormateurController;
use App\Http\Controllers\FormationController;
use Illuminate\Support\Facades\Mail;
use App\Mail\ApprenantInscritMail;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


// Routes pour authentification et déconnexion
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/qr/{matricule}', [QRCodeController::class, 'showQr']);
// Group routes that require authentication
Route::middleware('auth:api')->group(function () {

    // Routes pour la gestion des utilisateurs
    Route::prefix('user')->group(function () {
        Route::get('/role', [UserController::class, 'getRole']);
        Route::post('/update/information', [UserController::class, 'updateInformation']);
    });

    // Routes pour les apprenants, formateurs, chef de projet et vigiles
    Route::prefix('apprenant')->group(function () {
        Route::post('/inscrire', [ApprenantController::class, 'inscrireApprenant']);
        Route::get('/pointages/moi', [ApprenantController::class, 'MesPointages'])->name('pointage.moi');
    });
    Route::post('/formateur/inscrire', [AuthController::class, 'inscrireFormateur']);
    Route::post('/chef-de-projet/inscrire', [AuthController::class, 'inscrireChefDeProjet']);
    Route::post('/vigile/inscrire', [AuthController::class, 'inscrireVigile']);

    // Routes pour les promos
    Route::prefix('promos')->group(function () {
        Route::get('/', [PromoController::class, 'index']);
        Route::post('/', [PromoController::class, 'store']);
        Route::put('/', [PromoController::class, 'update']);
        Route::get('/formateur', [PromoController::class, 'mesPromos']);
        Route::get('/encours', [PromoController::class, 'mesPromosEncours']);
        Route::get('/terminer', [PromoController::class, 'mesPromosTermine']);
    });

    // Routes pour le pointage
    Route::prefix('pointage')->group(function () {
        Route::post('/arrivee', [PointageController::class, 'pointageArrivee']);
        Route::post('/depart', [PointageController::class, 'pointageDepart']);
        Route::get('/semaine', [PointageController::class, 'pointageParSemaine'])->name('pointage.semaine');
        Route::get('/all', [PointageController::class, 'afficherPointagesAujourdHui'])->name('pointage');
        Route::get('/promo/aujourdhui', [PointageController::class, 'afficherPointagesPromoAujourdHui'])->name('pointage.promo.aujourdhui');
        Route::get('/promo', [FormateurController::class, 'afficherPointagesPromo']);
    });

    // Routes pour les formateurs
    Route::prefix('formateurs')->group(function () {
        Route::get('/', [FormateurController::class, 'ListeFormateurs']);
        Route::get('/promotions', [FormateurController::class, 'getPromotions']);
    });

    // Routes pour les formations
    Route::resource('formations', FormationController::class);

    // Routes pour les fabriques
    Route::resource('fabriques', FabriqueController::class);

    // Autres routes sécurisées
    Route::post('apprenants/import', [ApprenantController::class, 'inscrireApprenantsExcel']);
    Route::get('/chefs-projet', [UserController::class, 'chefsProjet']);


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

});
});
// routes/web.php

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return response()->json([
        'user' => $request->user(),
        'role' => $request->user()->roles->pluck('name') // Renvoie les noms des rôles
    ]);
});
    