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
        $apprenants = User::role('Apprenant')->take(60)->get(); // Récupérer 30 apprenants
        $formateurs = User::role('Formateur')->take(3)->get(); // Récupérer 3 formateurs

        // Générer des pointages pour chaque mois : octobre, novembre
        $months = ['2024-10', '2024-11'];

        // Création des pointages pour les apprenants
        foreach ($apprenants as $apprenant) {
            foreach ($months as $month) {
                for ($day = 1; $day <= Carbon::parse("$month-01")->daysInMonth; $day++) {
                    $date = Carbon::parse("$month-$day");

                    // Ignorer les week-ends
                    if ($date->isWeekend()) {
                        continue;
                    }

                    // Générer une heure aléatoire et déterminer le type de pointage
                    $heure_present = $this->getRandomHeure();
                    $type = $this->getPointageType($heure_present);

                    Pointage::create([
                        'type' => $type,
                        'date' => $date->format('Y-m-d'),
                        'heure_present' => $heure_present,
                        'motif' => null,
                        'user_id' => $apprenant->id,
                        'created_by' => 21,
                    ]);
                }
            }
        }

        // Création des pointages pour les formateurs
        foreach ($formateurs as $formateur) {
            foreach ($months as $month) {
                for ($day = 1; $day <= Carbon::parse("$month-01")->daysInMonth; $day++) {
                    $date = Carbon::parse("$month-$day");

                    // Ignorer les week-ends
                    if ($date->isWeekend()) {
                        continue;
                    }

                    // Générer une heure aléatoire et déterminer le type de pointage
                    $heure_present = $this->getRandomHeure();
                    $type = $this->getPointageType($heure_present);

                    Pointage::create([
                        'type' => $type,
                        'date' => $date->format('Y-m-d'),
                        'heure_present' => $heure_present,
                        'motif' => null,
                        'user_id' => $formateur->id,
                        'created_by' => 21,
                    ]);
                }
            }
        }
    }

    /**
     * Obtenir un type de pointage en fonction de l'heure d'arrivée
     */
    private function getPointageType(string $heure): string
    {
        $pointageTime = Carbon::createFromFormat('H:i:s', $heure);

        // Présent si avant 09:00:00, sinon retard
        return $pointageTime->lte(Carbon::createFromTime(9, 0, 0)) ? 'present' : 'retard';
    }

    /**
     * Générer une heure d'arrivée aléatoire
     */
    private function getRandomHeure(): string
    {
        // Retourner une heure entre 08:00 et 09:30
        return Carbon::createFromTime(rand(8, 9), rand(0, 59), rand(0, 59))->format('H:i:s');
    }
}
