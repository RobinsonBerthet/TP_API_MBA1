<?php

use App\Http\Middleware\CheckAuth;
use App\Http\Middleware\CheckBearerToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// Route::middleware([CheckBearerToken::class])->group(function () {
//     Route::get('/api/documentation', function () {
//         return view('swagger.documentation'); // Retourne la vue contenant Swagger UI

//     });
// });