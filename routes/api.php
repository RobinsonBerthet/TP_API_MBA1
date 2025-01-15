<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HackController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\RulesController;

Route::post('/auth/register', [AuthController::class, 'register']);

Route::post('/auth/login', [AuthController::class, 'login']);

Route::post('/auth/logout', [AuthController::class, 'logout']);


Route::get('/user/me', [AuthController::class, 'me']);


Route::get('/emails/emailChecker/{email}', [HackController::class, 'emailChecker']);

Route::post('/emails/spam', [HackController::class, 'envoyerEmail']);

Route::post('/passwords/check', [HackController::class, 'checkPassword']);

Route::get('/passwords/generate', [HackController::class, 'generatePassword']);

Route::get('/domains/{domain}', [HackController::class, 'getSubdomains']);

Route::post('/attack/ddos', [HackController::class, 'ddos']);

Route::get('/identities/generate', [HackController::class, 'generateIdentity']);

Route::post('/phishing/page', [HackController::class, 'phishing']);

Route::post('/phishing/data', [HackController::class, 'getDataFromPhishing']);

Route::get('/images/random', [HackController::class, 'getRandomPerson']);

Route::post('/identity/crawl', [HackController::class, 'crawlerInformation']);


Route::get('/logs/last', [LogsController::class, 'getLastLogs']); // Pour récupérer les derniers logs

Route::get('/logs/user/{userId}', [LogsController::class, 'getLogsByUser']); // Pour récupérer les logs d'un utilisateur spécifique

Route::get('/logs/fonctionnalite/{fonctionnaliteId}', [LogsController::class, 'getLogsByFonctionnalite']); // Pour récupérer les logs liés à une fonctionnalité spécifique


Route::put('/rules', [RulesController::class, 'changeRules']);





