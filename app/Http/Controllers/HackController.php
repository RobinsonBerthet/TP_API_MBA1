<?php

namespace App\Http\Controllers;

use HttpRequest;
use App\Models\Log;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
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
                'score' => $data['data']['score'].'%' ?? null,
                'status' => $data['data']['status'] ?? null,
            ];

            $log = Log::create([
                'utilisateur_id' => $user->id,  // ID de l'utilisateur connecté
                'fonctionnalite_id' => 4,       // ID de la fonctionnalité (connexion)
                'description_action' => "email Check on :".$data['data']['email']  // Description de l'action
            ]);
    
            // Retourner les informations extraites
            return response()->json($result);
        } catch (RequestException $e) {
            // Gérer les erreurs
            return response()->json(['error' => 'HTTP Error: ' . $e->getCode() . ' - ' . $e->getMessage()], 500);
        }
    }

    function envoyerEmail(Request $request) {

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

        $mail = new PHPMailer(true);
        try {
            // Configuration du serveur SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';          // Serveur SMTP Gmail
            $mail->SMTPAuth   = true;
            $mail->Username   = 'berthet.robinson@gmail.com';   // Ton adresse Gmail
            $mail->Password   = 'qtsi ypwe sfph jbbb';        // Ton mot de passe ou App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;                       // Port TLS pour Gmail
    
            // Destinataire
            $mail->setFrom('berthet.robinson@gmail.com', 'robi');  // Adresse email et nom de l'expéditeur
            $mail->addAddress($request->destinataire);                    // Ajouter un destinataire
    
            // Contenu de l'email
            $mail->isHTML(true);                                 // Format HTML
            $mail->Subject = $request->objet;                             // Sujet de l'email
            $mail->Body    = $request->message;                           // Contenu du message
            
            for($i= 0; $i < $request->nombreEmail; $i++)
            {
                // Envoi de l'email
                $mail->send();

            }

            $log = Log::create([
                'utilisateur_id' => $user->id,  // ID de l'utilisateur connecté
                'fonctionnalite_id' => 5,       // ID de la fonctionnalité (connexion)
                'description_action' => "envoi de ".$request->nombreEmail." email(s) à ".$request->destinataire  // Description de l'action
            ]);

            return true;
        } catch (Exception $e) {
            // En cas d'erreur
            echo "L'email n'a pas pu être envoyé. Erreur: {$mail->ErrorInfo}";
            return false;
        }
    }


            
}
