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
                ]);

    
                // Générer un token JWT
                try {
                    // Générer un token JWT
                    $token = Auth::guard('api')->login($user);
                    $log = Log::create([
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
            $log = Log::create([
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
            $log = Log::create([
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
