<?php

namespace App\Http\Controllers;

use App\Models\Fonctionnalite;
use App\Models\Log;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LogsController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/getLastLogs",
     *     summary="Récupère les derniers logs d'activité",
     *     description="Cette méthode permet à un administrateur de récupérer les derniers logs d'activité, triés par date. Elle nécessite que l'utilisateur soit authentifié et possède le rôle d'administrateur.",
     *     operationId="getLastLogs",
     *     tags={"Logs"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"nbLogs"},
     *                 @OA\Property(property="nbLogs", type="integer", example=10, description="Le nombre de derniers logs à récupérer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Les derniers logs récupérés avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="user", type="string", description="Nom de l'utilisateur ayant effectué l'action"),
     *                 @OA\Property(property="fonctionnalite", type="string", description="Nom de la fonctionnalité associée à l'action"),
     *                 @OA\Property(property="date_action", type="string", format="date-time", description="Date et heure de l'action"),
     *                 @OA\Property(property="description_action", type="string", description="Description de l'action réalisée")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non authentifié",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Utilisateur non authentifié")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit pour l'utilisateur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Vous n'avez pas les droits pour accéder à cette ressource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur lors de la récupération des logs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function getLastLogs(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        //verifier que l'utilisateur est un admin (table role) role_id = 1
        if($user->role_id != 1){
            return response()->json(['error' => 'Vous n\'avez pas les droits pour accéder à cette ressource'], 403);
        }
        
        try{
            $logs = Log::all()->sortByDesc('date_action')->take($request->nbLogs);

            // pour chaque log recupère l'id de l'utilisateur qui a effectué l'action et recupère ensuite dans la table utilisateurs le nom et prenom par rapport à l'id
            foreach($logs as $log){

                $fonctionnalite = Fonctionnalite::find($log->fonctionnalite_id);
                $user = Utilisateur::find($log->utilisateur_id);
                
                if ($user) {
                    $log->user = $user->nom;
                } else {
                    $log->user = 'Utilisateur inconnu';
                }
                if ($fonctionnalite) {
                    $log->fonctionnalite = $fonctionnalite->nom_fonctionnalite;
                } else {
                    $log->fonctionnalite = 'Fonctionnalité inconnue';
                
                }
            }

            
            return response()->json($logs);

        }catch(\Exception $e){
            return response()->json(['error' => 'Erreur lors de la récupération des logs '.$e], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/getLogsByUser",
     *     summary="Récupère les logs d'un utilisateur spécifique",
     *     description="Cette méthode permet à un administrateur de récupérer les logs d'activité d'un utilisateur spécifique basé sur son email. Elle nécessite que l'utilisateur soit authentifié et possède le rôle d'administrateur.",
     *     operationId="getLogsByUser",
     *     tags={"Logs"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"email"},
     *                 @OA\Property(property="email", type="string", format="email", example="utilisateur@example.com", description="L'email de l'utilisateur pour lequel récupérer les logs")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Logs récupérés avec succès pour l'utilisateur",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="user", type="string", description="Nom de l'utilisateur ayant effectué l'action"),
     *                 @OA\Property(property="fonctionnalite", type="string", description="Nom de la fonctionnalité associée à l'action"),
     *                 @OA\Property(property="date_action", type="string", format="date-time", description="Date et heure de l'action"),
     *                 @OA\Property(property="description_action", type="string", description="Description de l'action réalisée")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non authentifié",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Utilisateur non authentifié")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit pour l'utilisateur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Vous n'avez pas les droits pour accéder à cette ressource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Utilisateur non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur lors de la récupération des logs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function getLogsByUser(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        //verifier que l'utilisateur est un admin (table role) role_id = 1
        if($user->role_id != 1){
            return response()->json(['error' => 'Vous n\'avez pas les droits pour accéder à cette ressource'], 403);
        }
    
        try {
            // Récupérer l'utilisateur recherché par email
            $utilisateurRecherche = Utilisateur::where('email', $request->email)->first();
    
            if (!$utilisateurRecherche) {
                return response()->json(['error' => 'Utilisateur non trouvé'], 404);
            }
    
            // Récupérer les logs de l'utilisateur trouvé
            $logs = Log::where('utilisateur_id', $utilisateurRecherche->id)->get();
    
            // Ajouter des informations utilisateur et fonctionnalité à chaque log
            foreach ($logs as $log) {
                $fonctionnalite = Fonctionnalite::find($log->fonctionnalite_id);
                $user = Utilisateur::find($log->utilisateur_id);
    
                $log->user = $user ? $user->nom : 'Utilisateur inconnu';
                $log->fonctionnalite = $fonctionnalite ? $fonctionnalite->nom_fonctionnalite : 'Fonctionnalité inconnue';
            }
    
            return response()->json($logs);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des logs: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/getLogsByFonctionnalite",
     *     summary="Récupère les derniers logs d'une fonctionnalité spécifique",
     *     description="Cette méthode permet à un administrateur de récupérer les derniers logs d'une fonctionnalité spécifique, identifiée par son nom. Elle nécessite que l'utilisateur soit authentifié et possède le rôle d'administrateur.",
     *     operationId="getLogsByFonctionnalite",
     *     tags={"Logs"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"nom_fonctionnalite"},
     *                 @OA\Property(property="nom_fonctionnalite", type="string", example="Recherche", description="Le nom de la fonctionnalité pour laquelle récupérer les logs")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Logs récupérés avec succès pour la fonctionnalité",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="user", type="string", description="Nom de l'utilisateur ayant effectué l'action"),
     *                 @OA\Property(property="fonctionnalite", type="string", description="Nom de la fonctionnalité associée à l'action"),
     *                 @OA\Property(property="date_action", type="string", format="date-time", description="Date et heure de l'action"),
     *                 @OA\Property(property="description_action", type="string", description="Description de l'action réalisée")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non authentifié",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Utilisateur non authentifié")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit pour l'utilisateur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Vous n'avez pas les droits pour accéder à cette ressource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Fonctionnalité non trouvée",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Fonctionnalité non trouvée")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur lors de la récupération des logs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function getLogsByFonctionnalite(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        //verifier que l'utilisateur est un admin (table role) role_id = 1
        if($user->role_id != 1){
            return response()->json(['error' => 'Vous n\'avez pas les droits pour accéder à cette ressource'], 403);
        }
    
        try {
            // Récupérer la fonctionnalité recherchée par nom
            $fonctionnaliteRecherche = Fonctionnalite::where('nom_fonctionnalite', $request->nom_fonctionnalite)->first();
    
            if (!$fonctionnaliteRecherche) {
                return response()->json(['error' => 'Fonctionnalité non trouvée'], 404);
            }
    
            // Récupérer les logs de la fonctionnalité trouvée
            $logs = Log::where('fonctionnalite_id', $fonctionnaliteRecherche->id)->get();

            $logs = $logs->sortByDesc('date_action');
    
            // Ajouter des informations utilisateur et fonctionnalité à chaque log
            foreach ($logs as $log) {
                $fonctionnalite = Fonctionnalite::find($log->fonctionnalite_id);
                $user = Utilisateur::find($log->utilisateur_id);
    
                $log->user = $user ? $user->nom : 'Utilisateur inconnu';
                $log->fonctionnalite = $fonctionnalite ? $fonctionnalite->nom_fonctionnalite : 'Fonctionnalité inconnue';
            }
    
            return response()->json($logs);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des logs: ' . $e->getMessage()], 500);
        }
    }
}
