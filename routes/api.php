<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApprenantController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/apprenant/inscrire', [ApprenantController::class, 'inscrireApprenant']);
Route::post('/login', [AuthController::class, 'login']);


use App\Notifications\ApprenantInscriptionNotification;
use Illuminate\Support\Facades\Notification;

// Ajoutez ce code dans une route ou un contrôleur
Route::get('/test-notification', function () {
    $user = \App\Models\User::first(); // Assurez-vous d'avoir un utilisateur pour tester
    Notification::route('mail', $user->email)
        ->notify(new ApprenantInscriptionNotification($user, 'password123'));
    return 'Notification sent!';
});
