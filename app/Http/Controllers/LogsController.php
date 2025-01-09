<?php

namespace App\Http\Controllers;

use App\Models\Fonctionnalite;
use App\Models\Log;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LogsController extends Controller
{
    public function getLastLogs(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
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

    //ecris une fonction qui va recuperer les logs par rapport à un utilisateur
    public function getLogsByUser(Request $request)
    {
        $user = Auth::user();
    
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
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
    //je veux une fonction qui récupères les dernières actions sur une fonctionnalité spécifique
    public function getLastLogsByFonctionnalite(Request $request)
    {
        $user = Auth::user();
    
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
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
