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

    /**
     * @OA\Post(
     *     path="/api/changeRules",
     *     summary="Ajoute ou modifie les droits d'accès pour une fonctionnalité et un rôle",
     *     description="Cette méthode permet à un administrateur d'ajouter ou de modifier les droits d'accès pour une fonctionnalité donnée et un rôle spécifique. Si un droit existe déjà, il sera modifié, sinon il sera créé.",
     *     operationId="changeRules",
     *     tags={"Droits"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"fonctionnalite_id", "role_id"},
     *                 @OA\Property(property="fonctionnalite_id", type="integer", example=1, description="L'ID de la fonctionnalité pour laquelle attribuer ou modifier les droits"),
     *                 @OA\Property(property="role_id", type="integer", example=2, description="L'ID du rôle pour lequel attribuer ou modifier les droits")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Droit ajouté ou modifié avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="result", type="string", example="Droit ajouté")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Droit déjà existant",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Droit déjà existant")
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
     *         description="Erreur lors de la modification des droits",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
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
