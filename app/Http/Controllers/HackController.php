<?php

namespace App\Http\Controllers;

use HttpRequest;
use App\Models\Log;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpKernel\Exception\HttpException;


class HackController extends Controller
{
    public function emailChecker($email)
    {
        $user = Auth::user();
    
        if (!$user) {
            // Si l'utilisateur n'est pas authentifié, renvoyer une réponse avec une erreur 401
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        try{

        } catch (\Exception $e) {
            // Gérer les erreurs liées à la création du log
            return response()->json(['error' => 'Erreur lors de l\'enregistrement du log: ' . $e->getMessage()], 500);
        }
        $client = new Client();
        $url = 'https://api.hunter.io/v2/email-verifier?email='.$email.'&api_key=a408bf05f8c0b21d39120be64204a5265b1db4a6';

        try {
            $response = $client->request('GET', $url);
        
            // Décoder la réponse JSON
            $data = json_decode($response->getBody(), true);
    
            // Extraire les informations nécessaires
            $result = [
                'email' => $data['data']['email'] ?? null,
                'score' => $data['data']['score'] ?? null,
                'status' => $data['data']['status'] ?? null,
            ];
    
            // Retourner les informations extraites
            return response()->json($result);
        } catch (RequestException $e) {
            // Gérer les erreurs
            return response()->json(['error' => 'HTTP Error: ' . $e->getCode() . ' - ' . $e->getMessage()], 500);
        }
    }


            
}
