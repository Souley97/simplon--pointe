<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Justificatif;
use App\Models\Pointage;

class JustificatifsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assurez-vous d'avoir des pointages dans la base de données
        $pointages = Pointage::all();

        // Créer des justificatifs pour certains pointages
        foreach ($pointages as $pointage) {
            if ($pointage->type == 'absenc') {  // Créer un justificatif uniquement pour les absences ou retards
                Justificatif::create([
                    'date' => now(),
                    'description' => 'Justificatif pour ' . $pointage->type,
                    'document' => 'path/to/document.pdf',  // Mettre à jour avec le chemin réel du document
                    'pointage_id' => $pointage->id,
                ]);
            }
        }
    }
}
