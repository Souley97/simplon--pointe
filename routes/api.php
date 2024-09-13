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
