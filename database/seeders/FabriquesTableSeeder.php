<?php

namespace Database\Seeders;

use App\Models\Fabrique;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FabriquesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Fabrique::create([
            'nom' => 'Simplon Dakar',
            'localisation' => 'Dakar, Sénégal',
        ]);

        Fabrique::create([
            'nom' => 'Simplon Pikine',
            'localisation' => 'Pikine, Sénégal',
        ]);

        Fabrique::create([
            'nom' => 'Simplon Mermoz',
            'localisation' => 'Mermoz, Sénégal',
        ]);

        Fabrique::create([
            'nom' => 'Simplon Keur massar',
            'localisation' => 'Keur massar, Sénégal',
        ]);
    }
}
