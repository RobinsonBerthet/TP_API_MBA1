<?php

namespace App\Http\Controllers;


/**
 * @OA\Info(
 *     title="API HackR",
 *     version="1.0",
 *     description="Voici la documentation de l'API HackR. Vous pouvez tester les différentes routes (Authentification, hack, logs & rules) en utilisant l'interface Swagger."
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

abstract class Controller
{

}
