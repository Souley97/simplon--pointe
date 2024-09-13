<?php

namespace Database\Seeders;

use App\Models\Formation;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FormationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Formation::create(['nom' => 'Développement Web et Web Mobile']);
        Formation::create(['nom' => 'Développement Frontend']);
        Formation::create(['nom' => 'Développement Backend']);
        Formation::create(['nom' => 'IOT']);
        Formation::create(['nom' => 'Reference digital']);
        Formation::create(['nom' => 'Gestion de projet']);
    }
}
