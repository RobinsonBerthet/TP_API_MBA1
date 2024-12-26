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
}
