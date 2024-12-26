<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HackController;
use App\Http\Controllers\LogsController;

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/me', [AuthController::class, 'me']);

Route::get('/emailChecker/{email}', [HackController::class, 'emailChecker']);

Route::post('/spam', [HackController::class, 'envoyerEmail']);

Route::post('/check-password', [HackController::class, 'checkPassword']);

Route::get('/modify-html', [HackController::class, 'modifyHtml']);

Route::get('/passwordGenerator', [HackController::class, 'generatePassword']);

Route::get('/subdomains/{domain}', [HackController::class, 'getSubdomains']);

Route::post('/ddos', [HackController::class, 'ddos']);

Route::get('/generate-identity', [HackController::class, 'generateIdentity']);

Route::post('/generateContentWithGemini', [HackController::class, 'generateContentWithGemini']);

Route::post('/phishing', [HackController::class, 'phishing']);

Route::post('/getData', [HackController::class, 'getData']);

Route::get('/getRandomPerson', [HackController::class, 'getRandomPerson']);

Route::post('/crawlerInformation', [HackController::class, 'crawlerInformation']);

Route::post('/getLastLogs', [LogsController::class, 'getLastLogs']);
