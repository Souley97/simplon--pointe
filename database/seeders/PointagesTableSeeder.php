<?php

namespace Database\Seeders;

use App\Models\Pointage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PointagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer quelques utilisateurs (apprenants et formateurs)
        $apprenants = User::role('Apprenant')->take(30)->get(); // Récupérer 10 apprenants
        $formateurs = User::role('Formateur')->take(3)->get(); // Récupérer 3 formateurs





        // Création des pointages pour les apprenants
        foreach ($apprenants as $apprenant) {
            Pointage::create([
                'type' => 'present', // 'present', 'absence', 'retard'
                'date' => "2024-10-21", // Date aléatoire
                'heure_present' => Carbon::now()->subHours(rand(1, 4))->format('H:i:s'), // Heure d'arrivée aléatoire
                'motif' => null,
                'user_id' => $apprenant->id,
                'created_by' =>32,



            ]);
            Pointage::create([
                'type' => 'present', // 'present', 'absence', 'retard'
                'date' => "2024-10-22", // Date aléatoire
                'heure_present' => Carbon::now()->subHours(rand(1, 4))->format('H:i:s'), // Heure d'arrivée aléatoire
                'motif' => null,
                'user_id' => $apprenant->id,
                'created_by' =>32,



            ]);

            Pointage::create([
                'type' => 'retard',
                'date' => "2024-10-19",
                'heure_present' => Carbon::now()->subHours(rand(1, 4))->format('H:i:s'),
                'motif' => 'Problème de transport',
                'user_id' => $apprenant->id,
                'created_by' =>32,



            ]);

            Pointage::create([
                'type' => 'absence',
                'date' => "2024-10-23",
                'heure_present' => null,
                'motif' => 'Maladie',
                'user_id' => $apprenant->id,
                'created_by' =>32,

            ]);
        }

        // Création des pointages pour les formateurs
        foreach ($formateurs as $formateur) {
            Pointage::create([
                'type' => 'present',
                'date' => "2024-11-12",
                'heure_present' => Carbon::now()->subHours(rand(1, 4))->format('H:i:s'),
                'motif' => null,
                'user_id' => $formateur->id,
                'created_by' =>31,
            ]);

            Pointage::create([
                'type' => 'present',
                'date' => "2024-10-18",
                'heure_present' => Carbon::now()->subHours(rand(1, 4))->format('H:i:s'),
                'motif' => 'Réunion externe',
                'user_id' => $formateur->id,
                'created_by' =>31,
            ]);

            Pointage::create([
                'type' => 'present',
                'date' => Carbon::now()->subDays(rand(0, 29))->format('Y-m-d'),
                'heure_present' => Carbon::now()->subHours(rand(1, 4))->format('H:i:s'),
                'motif' => null,
                'user_id' => $formateur->id,
                'created_by' =>31,
            ]);
            Pointage::create([
                'type' => 'present',
                'date' => Carbon::now()->subDays(rand(0, 29))->format('Y-m-d'),
                'heure_present' => Carbon::now()->subHours(rand(1, 4))->format('H:i:s'),
                'motif' => null,
                'user_id' => $formateur->id,
                'created_by' =>32,
            ]);
            Pointage::create([
                'type' => 'present',
                'date' => Carbon::now()->subDays(rand(0, 29))->format('Y-m-d'),
                'heure_present' => Carbon::now()->subHours(rand(1, 4))->format('H:i:s'),
                'motif' => null,
                'user_id' => $formateur->id,
                'created_by' =>32,
            ]);
            Pointage::create([
                'type' => 'absence',
                'date' => Carbon::now()->subDays(rand(0, 29))->format('Y-m-d'),
                'heure_present' => "08:29:08",
                'motif' => 'Réunion externe',
                'user_id' => $formateur->id,
                'created_by' =>32,
            ]);
        }
    }
}
