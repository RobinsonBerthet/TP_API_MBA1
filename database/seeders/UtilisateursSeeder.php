<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UtilisateursSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('utilisateurs')->insert([
            [
                'nom' => 'robinson',
                'email' => 'robinson.berthet@gmail.com',
                'motDePasse' => Hash::make('abcd1234'),
                'role_id' => 1,
                'dateCreation' => '2025-01-15 14:00:00',
            ],
        ]);
    }
}
