<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FonctionnaliteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('fonctionnalites')->insert([
            [
                'nom_fonctionnalite' => 'inscription',
                'description' => "Inscription d'un utilisateur",
            ],
            [
                'nom_fonctionnalite' => 'connexion',
                'description' => "Connexion d'un utilisateur",
            ],
            [
                'nom_fonctionnalite' => 'me',
                'description' => "Permet d'obtenir les informations sur l'utilisateur connecté",
            ],
            [
                'nom_fonctionnalite' => 'email checker',
                'description' => "Vérification de l'email saisi",
            ],
            [
                'nom_fonctionnalite' => 'email spam',
                'description' => "Envoi d'un nombre d'emails spam à une personne définie",
            ],
            [
                'nom_fonctionnalite' => 'déconnexion',
                'description' => "Déconnexion d'un utilisateur",
            ],
            [
                'nom_fonctionnalite' => 'password Checker',
                'description' => "Vérifie si un mot de passe est présent dans la liste des passwords les plus courants de ce fichier txt : https://raw.githubusercontent.com/danielmiessler/SecLists/master/Passwords/Common-Credentials/10k-most-common.txt",
            ],
            [
                'nom_fonctionnalite' => 'password generator',
                'description' => "Génération d'un mot de passe fort",
            ],
            [
                'nom_fonctionnalite' => 'récupérateur de domaine',
                'description' => "Récupération des domaines & sous-domaines d'un nom de domaine",
            ],
            [
                'nom_fonctionnalite' => 'générateur d\'identité fictive',
                'description' => "Génère une identité fictive grâce à la librairie Faker PHP",
            ],
            [
                'nom_fonctionnalite' => 'ddos',
                'description' => "Une attaque en déni de service (DDoS)",
            ],
            [
                'nom_fonctionnalite' => 'phishing',
                'description' => "Phishing en récupérant une page de connexion existante",
            ],
            [
                'nom_fonctionnalite' => 'getData',
                'description' => "Récupère les données du phishing",
            ],
            [
                'nom_fonctionnalite' => 'crawler d\'information',
                'description' => "Récupère des informations sur une personne donnée",
            ],
            [
                'nom_fonctionnalite' => 'génération d\'une personne aléatoire',
                'description' => "Génère une image d'une personne qui n'existe pas (IA)",
            ],
        ]);
    }
}
