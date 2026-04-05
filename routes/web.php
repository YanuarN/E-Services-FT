<?php

use App\Http\Controllers\DocumentVerificationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::get('/verify/{letterType}/{token}', [DocumentVerificationController::class, 'show'])
    ->name('verification.show');
