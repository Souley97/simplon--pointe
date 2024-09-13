<?php

namespace Database\Seeders;

use App\Models\Pointage;
use App\Models\User;
use Illuminate\Database\Seeder;

class PointagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer quelques utilisateurs (apprenants et formateurs)
        $apprenants = User::role('Apprenant')->take(10)->get(); // Récupérer 10 apprenants
        $formateurs = User::role('Formateur')->take(3)->get(); // Récupérer 3 formateurs

        // Création des pointages pour les apprenants
        foreach ($apprenants as $apprenant) {
            Pointage::create([
                'type' => 'present', // 'present', 'absence', 'retard'
                'date' => now()->subDays(rand(0, 30)), // Date aléatoire dans les 30 derniers jours
                'heure_present' => now()->subHours(rand(1, 4)), // Heure d'arrivée aléatoire
                'motif' => null, // Aucune absence
                'user_id' => $apprenant->id,
            ]);

            Pointage::create([
                'type' => 'retard',
                'date' => now()->subDays(rand(0, 30)),
                'heure_present' => now()->subHours(rand(1, 4)),
                'motif' => 'Problème de transport',
                'user_id' => $apprenant->id,
            ]);

            Pointage::create([
                'type' => 'absence',
                'date' => now()->subDays(rand(0, 30)),
                'heure_present' => null,
                'motif' => 'Maladie',
                'user_id' => $apprenant->id,
            ]);
        }

        // Création des pointages pour les formateurs
        foreach ($formateurs as $formateur) {
            Pointage::create([
                'type' => 'present',
                'date' => now()->subDays(rand(0, 30)),
                'heure_present' => now()->subHours(rand(1, 4)),
                'motif' => null,
                'user_id' => $formateur->id,
            ]);

            Pointage::create([
                'type' => 'absence',
                'date' => now()->subDays(rand(0, 30)),
                'heure_present' => null,
                'motif' => 'Réunion externe',
                'user_id' => $formateur->id,
            ]);
        }
    }
}
