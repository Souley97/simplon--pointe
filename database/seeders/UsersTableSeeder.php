<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Promo;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les rôles s'ils n'existent pas déjà
        Role::firstOrCreate(['name' => 'Formateur']);
        Role::firstOrCreate(['name' => 'Vigile']);
        Role::firstOrCreate(['name' => 'Administrateur']);
        Role::firstOrCreate(['name' => 'Chef de projet']);

        // Créer un Formateur
        $formateur = User::create([
            'nom' => 'KANE',
            'prenom' => 'Samba',
            'matricule' => 'formateur01',
            'telephone' => '7766554433',
            'adresse' => 'Dakar',
            'email' => 'sbk@dimplon.com',
            'password' => bcrypt('password'),
            'statut' => true,
            'sexe' => 'homme',
            'photo_profile' => 'formateur.jpg',
        ]);
        $formateur->assignRole('Formateur');

        // Créer un Formateur


        // Assigner le Formateur à une promotion
        $promo1 = Promo::find(1); // Associer à une promo existante
        $formateur->promos()->attach($promo1);

        // Créer un Formateur
        $formateur2 = User::create([
            'nom' => 'TALLA',
            'prenom' => 'Chekh Saliou',
            'matricule' => 'formateur02',
            'telephone' => '7744332211',
            'adresse' => 'Dakar',
            'email' => 'cs@dimplon.com',
            'password' => bcrypt('password'),
            'statut' => true,
            'sexe' => 'homme',
            'photo_profile' => 'formateur.jpg',
        ]);
        $formateur2->assignRole('Formateur');

        // Assigner le Formateur à une autre promotion
        $promo2 = Promo::find(2); // Associer à une autre promo existante
        $formateur2->promos()->attach($promo2);
        // Créer un Formateur






        // Créer un Vigile
        $vigile = User::create([
            'nom' => 'Sarr',
            'prenom' => 'Adama',
            'matricule' => 'vigile01',
            'telephone' => '7788990011',
            'adresse' => 'Rufisque',
            'email' => 'adama.sarr@simplon.com',
            'password' => bcrypt('password'),
            'statut' => true,
            'sexe' => 'homme',
            'photo_profile' => 'vigile.jpg',
        ]);
        $vigile->assignRole('Vigile');

        // Créer un Administrateur
        $administrateur = User::create([
            'nom' => 'Ndiaye',
            'prenom' => 'Fatou',
            'matricule' => 'admin01',
            'telephone' => '7700112233',
            'adresse' => 'Saint-Louis',
            'email' => 'fatou.ndiaye@simplon.com',
            'password' => bcrypt('adminpassword'),
            'statut' => true,
            'sexe' => 'femme',
            'photo_profile' => 'admin.jpg',
        ]);
        $administrateur->assignRole('Administrateur');

        // Créer un Chef de projet
        $chefDeProjet = User::create([
            'nom' => 'Fall',
            'prenom' => 'Ibrahima',
            'matricule' => 'chefprojet01',
            'telephone' => '7711223344',
            'adresse' => 'Thiès',
            'email' => 'ibrahima.fall@simplon.com',
            'password' => bcrypt('password'),
            'statut' => true,
            'sexe' => 'homme',
            'photo_profile' => 'chefprojet.jpg',
        ]);
        $chefDeProjet->assignRole('Chef de projet');

        // Assigner le Chef de projet à une promotion
        $promo2 = Promo::find(2); // Associer à une autre promo existante
        $chefDeProjet->promos()->attach($promo2);
    }
}
