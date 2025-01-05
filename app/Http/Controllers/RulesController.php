<?php

namespace App\Http\Controllers;

use App\Models\Droit;
use App\Models\Log;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use App\Models\Fonctionnalite;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;


class RulesController extends Controller
{
    public function changeRules(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        //verifier que l'utilisateur est un admin (table role) role_id = 1
        if($user->role_id != 1){
            return response()->json(['error' => 'Vous n\'avez pas les droits pour accéder à cette ressource'], 403);
        }

        //ajoute ou modifie dans la table droits une nouvelle ligne avec l'id de la fonctionnalité et le role_id, verifier en amont que la fonctionnalité n'a pas de droits deja sinon la modifier si la ligne est deja présente ne rien faire et repondre droit deja existant
        try{
            $fonctionnalite = Droit::where('fonctionnalite_id', $request->fonctionnalite_id)->first();

            if(!$fonctionnalite){
                //create in droit table
                $droit = new Droit();
                $droit->fonctionnalite_id = $request->fonctionnalite_id;
                $droit->role_id = $request->role_id;
                $droit->save();

                return response()->json(['result' => 'Droit ajouté'], 200);

            }
            else
            {
                //update in droit table or response droit deja existant
                $droit = Droit::where('fonctionnalite_id', $request->fonctionnalite_id)->where('role_id', $request->role_id)->first();
                if($droit){
                    return response()->json(['error' => 'Droit déjà existant'], 400);
                }
                else
                {
                    $droit = Droit::where('fonctionnalite_id', $request->fonctionnalite_id)->first();
                    $droit->role_id = $request->role_id;
                    $droit->save();

                    return response()->json(['result' => 'Droit modifié'], 200);

                }
            }

        }
        catch(\Exception $e){
            return response()->json(['error' => 'Erreur lors de la modification des droits '.$e], 500);
        }
    }

}
