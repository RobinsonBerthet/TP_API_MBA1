<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HackController;

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/me', [AuthController::class, 'me']);

Route::get('/emailChecker/{email}', [HackController::class, 'emailChecker']);

Route::post('/spam', [HackController::class, 'envoyerEmail']);