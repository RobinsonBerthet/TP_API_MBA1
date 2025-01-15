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
     * @OA\Get(
     *     path="/api/logs/last",
     *     summary="Récupère les derniers logs d'activité",
     *     description="Cette méthode permet à un administrateur de récupérer les derniers logs d'activité, triés par date. Elle nécessite que l'utilisateur soit authentifié et possède le rôle d'administrateur.",
     *     operationId="getLastLogs",
     *     tags={"Logs"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="nbLogs",
     *         in="query",
     *         description="Le nombre de derniers logs à récupérer",
     *         required=false, 
     *         @OA\Schema(
     *             type="integer",
     *             example=10
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
    
        // Vérifie si l'utilisateur est authentifié
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }
    
        // Vérifie si l'utilisateur a les droits d'administrateur
        if ($user->role_id != 1) {
            return response()->json(['error' => 'Vous n\'avez pas les droits pour accéder à cette ressource'], 403);
        }
    
        try {
            // Récupère le paramètre de la requête GET pour le nombre de logs
            $nbLogs = $request->query('nbLogs', 10); // Valeur par défaut : 10 logs
    
            // Récupère les derniers logs
            $logs = Log::orderByDesc('date_action')->take($nbLogs)->get();
    
            // Ajoute des informations utilisateur et fonctionnalité à chaque log
            foreach ($logs as $log) {
                $fonctionnalite = Fonctionnalite::find($log->fonctionnalite_id);
                $utilisateur = Utilisateur::find($log->utilisateur_id);
    
                $log->user = $utilisateur ? $utilisateur->nom : 'Utilisateur inconnu';
                $log->fonctionnalite = $fonctionnalite ? $fonctionnalite->nom_fonctionnalite : 'Fonctionnalité inconnue';
            }
    
            return response()->json($logs);
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des logs : ' . $e->getMessage()], 500);
        }
    }
    

    /**
     * @OA\Get(
     *     path="/api/logs/user/{email}",
     *     summary="Récupère les logs d'un utilisateur spécifique",
     *     description="Cette méthode permet à un administrateur de récupérer les logs d'activité d'un utilisateur spécifique basé sur son email. Elle nécessite que l'utilisateur soit authentifié et possède le rôle d'administrateur.",
     *     operationId="getLogsByUser",
     *     tags={"Logs"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         description="L'email de l'utilisateur pour lequel récupérer les logs",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="email",
     *             example="utilisateur@example.com"
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
    public function getLogsByUser($email)
    {
        $authUser = Auth::user();

        // Vérifie si l'utilisateur est authentifié
        if (!$authUser) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        // Vérifie si l'utilisateur a les droits d'administrateur
        if ($authUser->role_id != 1) {
            return response()->json(['error' => 'Vous n\'avez pas les droits pour accéder à cette ressource'], 403);
        }

        try {
            // Récupérer l'utilisateur recherché par email
            $utilisateurRecherche = Utilisateur::where('email', $email)->first();

            if (!$utilisateurRecherche) {
                return response()->json(['error' => 'Utilisateur non trouvé'], 404);
            }

            // Récupérer les logs de l'utilisateur trouvé
            $logs = Log::where('utilisateur_id', $utilisateurRecherche->id)->get();

            // Ajouter des informations utilisateur et fonctionnalité à chaque log
            foreach ($logs as $log) {
                $fonctionnalite = Fonctionnalite::find($log->fonctionnalite_id);
                $log->user = $utilisateurRecherche->nom;
                $log->fonctionnalite = $fonctionnalite ? $fonctionnalite->nom_fonctionnalite : 'Fonctionnalité inconnue';
            }

            return response()->json($logs);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des logs : ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/logs/fonctionnalite/{fonctionnalite_id}",
     *     summary="Récupère les derniers logs d'une fonctionnalité spécifique",
     *     description="Cette méthode permet à un administrateur de récupérer les derniers logs d'une fonctionnalité spécifique, identifiée par son ID. Elle nécessite que l'utilisateur soit authentifié et possède le rôle d'administrateur.",
     *     operationId="getLogsByFonctionnalite",
     *     tags={"Logs"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="fonctionnalite_id",
     *         in="path",
     *         description="L'ID de la fonctionnalité pour laquelle récupérer les logs",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
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
    public function getLogsByFonctionnalite($fonctionnalite_id)
    {
        $user = Auth::user();

        // Vérifie si l'utilisateur est authentifié
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        // Vérifie si l'utilisateur a les droits d'administrateur
        if ($user->role_id != 1) {
            return response()->json(['error' => 'Vous n\'avez pas les droits pour accéder à cette ressource'], 403);
        }

        try {
            // Récupérer la fonctionnalité recherchée par ID
            $fonctionnaliteRecherche = Fonctionnalite::find($fonctionnalite_id);

            if (!$fonctionnaliteRecherche) {
                return response()->json(['error' => 'Fonctionnalité non trouvée'], 404);
            }

            // Récupérer les logs liés à la fonctionnalité
            $logs = Log::where('fonctionnalite_id', $fonctionnalite_id)->get();

            // Trier les logs par date d'action descendante
            $logs = $logs->sortByDesc('date_action');

            // Ajouter des informations utilisateur et fonctionnalité à chaque log
            foreach ($logs as $log) {
                $user = Utilisateur::find($log->utilisateur_id);
                $log->user = $user ? $user->nom : 'Utilisateur inconnu';
                $log->fonctionnalite = $fonctionnaliteRecherche->nom_fonctionnalite;
            }

            return response()->json($logs);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des logs : ' . $e->getMessage()], 500);
        }
    }   
}
