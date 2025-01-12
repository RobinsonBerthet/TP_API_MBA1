<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

        /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Inscription de l'utilisateur",
     *     operationId="registerUser",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="Password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User created successfully"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="authorisation", type="object", 
     *                 @OA\Property(property="token", type="string", example="token"),
     *                 @OA\Property(property="type", type="string", example="bearer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation"
     *     )
     * )
     */

    // Inscription de l'utilisateur
        // Inscription de l'utilisateur
        public function register(Request $request)
        {
            // Valider les données d'inscription
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:utilisateurs,email', // Vérification unique sur la table 'utilisateurs'
                'password' => 'required|min:8|confirmed', 
            ]);
      
            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }
    
            // Récupérer les données validées
            $validatedData = $validator->validated();


    
            // Créer un nouvel utilisateur
            try {
                $user = Utilisateur::create([
                    'nom' => $validatedData['name'],  // Champ 'nom' dans la table
                    'email' => $validatedData['email'],
                    'motDePasse' => Hash::make($validatedData['password']), // Champ 'motDePasse' dans la table
                    'role_id' => 2,  // ID du rôle (1 = admin)
                ]);

    
                // Générer un token JWT
                try {
                    // Générer un token JWT
                    $token = Auth::guard('api')->login($user);
                    Log::create([
                        'utilisateur_id'=> $user->id,
                        'fonctionnalite_id'=> 1,
                        'description_action'=> "Inscription Réussie",

                    ]);

                } catch (\Exception $e) {
                    return response()->json(['error' => 'Erreur lors de la génération du token JWT: ' . $e->getMessage()], 500);
                }
                
    
                // Retourner une réponse avec succès
                return response()->json([
                    'status' => 'success',
                    'message' => 'User created successfully',
                    'user' => $user,
                    'authorisation' => [
                        'token' => $token,
                        'type' => 'bearer',
                    ]
                ], 201);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Erreur lors de la création de l\'utilisateur: ' . $e->getMessage()], 500);
            }
        }
    

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Connexion de l'utilisateur",
     *     operationId="loginUser",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="haitam.elq@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */

    // Connexion de l'utilisateur
    public function login(Request $request)
    {
        // Change 'motDePasse' en 'password'
        $credentials = $request->only('email', 'password'); 
    
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        // Récupérer l'utilisateur connecté
        $user = Auth::user();
    
        // Enregistrer un log en base de données pour l'action de connexion
        try {
            Log::create([
                'utilisateur_id' => $user->id,  // ID de l'utilisateur connecté
                'fonctionnalite_id' => 2,       // ID de la fonctionnalité (connexion)
                'description_action' => "Connexion réussie",  // Description de l'action
            ]);
        } catch (\Exception $e) {
            // Gérer les erreurs liées à la création du log
            return response()->json(['error' => 'Erreur lors de l\'enregistrement du log: ' . $e->getMessage()], 500);
        }
    
        return response()->json(['token' => $token]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Déconnexion de l'utilisateur",
     *     operationId="logoutUser",
     *     tags={"Authentification"},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token invalide"
     *     )
     * )
     */

    public function logout()
    {
        try {

            $user = Auth::user();
            
            Log::create([
                'utilisateur_id' => $user->id,  // ID de l'utilisateur connecté
                'fonctionnalite_id' => 6,       // ID de la fonctionnalité (déconnexion)
                'description_action' => "Déconnexion réussie",  // Description de l'action
            ]);
            // Invalider le token JWT
            JWTAuth::invalidate(JWTAuth::getToken());
            
            // Enregistrer un log en base de données pour l'action de déconnexion

            
            return response()->json(['message' => 'Déconnexion réussie']);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token déjà invalide'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la déconnexion: ' . $e->getMessage()], 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/me",
     *     summary="Récupération de l'utilisateur authentifié",
     *     operationId="getAuthenticatedUser",
     *     tags={"Authentification"},
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="nom", type="string", example="John Doe"),
     *             @OA\Property(property="statut", type="string", example="actif"),
     *             @OA\Property(property="date_creation", type="string", format="date-time", example="2024-10-26T12:30:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non authentifié"
     *     )
     * )
     */

    // Récupération de l'utilisateur authentifié
    public function me()
    {
        // Vérifier si l'utilisateur est authentifié
        $user = Auth::user();
    
        if (!$user) {
            // Si l'utilisateur n'est pas authentifié, renvoyer une réponse avec une erreur 401
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }
    
        try {
            // Enregistrer un log en base de données pour la consultation du profil
            Log::create([
                'utilisateur_id' => $user->id,  // ID de l'utilisateur connecté
                'fonctionnalite_id' => 3,       // ID de la fonctionnalité (consultation profil)
                'description_action' => "consultation profil",  // Description de l'action
            ]);
    
            // Retourner les informations du profil de l'utilisateur
            return response()->json([
                'email' => $user->email,
                'nom' => $user->nom,
                'statut' => $user->statut,
                'date_creation' => $user->dateCreation
            ]);
        } catch (\Exception $e) {
            // Gérer les erreurs liées à la création du log
            return response()->json(['error' => 'Erreur lors de l\'enregistrement du log: ' . $e->getMessage()], 500);
        }
    }
    
}
