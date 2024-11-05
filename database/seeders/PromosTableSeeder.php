<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Promo;
use App\Models\Formation;
use App\Models\User;
use App\Models\Fabrique;

class PromosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer quelques fabriques, formateurs, chefs de projet et formations
        $fabriques = Fabrique::all();
        $formateurs = User::role('Formateur')->get();
        $chefsDeProjet = User::role('ChefDeProjet')->get();
        $formations = Formation::all();

        // Crée plusieurs promotions
        Promo::create([
            'nom' => 'Promo 7',
            'date_debut' => '2023-11-24',
            'date_fin' => '2024-10-17',
            'statut' => 'encours',
            'horaire' => '09:00',
            'fabrique_id' => 3, // Associe une fabrique aléatoirement
            'formateur_id' => 30, // Associe un formateur aléatoirement
            'chef_projet_id' => $chefsDeProjet->random()->id, // Associe un chef de projet aléatoirement
            'formation_id' => $formations->random()->id, // Associe une formation aléatoirement
        ]);

        Promo::create([
            'nom' => 'Promo 2',
            'date_debut' => '2024-02-01',
            'date_fin' => '2024-11-30',
            'statut' => 'encours',
            'horaire' => '09:00',

            'fabrique_id' => $fabriques->random()->id,
            'formateur_id' => $formateurs->random()->id,
            'chef_projet_id' => $chefsDeProjet->random()->id,
            'formation_id' => $formations->random()->id,
        ]);

        Promo::create([
            'nom' => 'Promo 3',
            'date_debut' => '2024-03-01',
            'date_fin' => '2024-08-01',
            'statut' => 'termine',
            'horaire' => '10:00',

            'fabrique_id' => $fabriques->random()->id,
            'formateur_id' => $formateurs->random()->id,
            'chef_projet_id' => $chefsDeProjet->random()->id,
            'formation_id' => $formations->random()->id,
        ]);

        // Ajoute des apprenants à une promotion
        $promo1 = Promo::find(1);
        $promo1->apprenants()->attach([1, 2, 3, 4, 5]); // Ajoute 5 apprenants à la promo 1

        $promo2 = Promo::find(2);
        $promo2->apprenants()->attach([6, 7, 8, 9, 10]); // Ajoute 5 apprenants à la promo 2

        $promo3 = Promo::find(3);
        $promo3->apprenants()->attach([11, 12, 13, 14, 15]); // Ajoute 5 apprenants à la promo 3

    }
}
