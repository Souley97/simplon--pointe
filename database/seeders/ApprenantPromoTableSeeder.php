<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ApprenantPromoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Role::firstOrCreate(['name' => 'Apprenant']);
        $apprenant = User::create([
            'nom' => 'NDIAYE',
            'prenom' => 'Souleymane',
            'matricule' => 'souleymane24',
            'telephone' => '766666666',
            'adresse' => 'malika',
            'email' => 'souleymane9700@gmail.com',
            'mot_de_passe' => bcrypt('password'),
            'statut' => true,
            'sexe' => 'homme',
            'photo_profile' => 'photo.jpg',
        ]);
        $apprenant->assignRole('Apprenant');
        // $apprenant->promotions()->attach(1);

        $apprenant = User::create([
            'nom' => 'SANE',
            'prenom' => 'Cheikh',
            'matricule' => 'cheikhp724',
            'telephone' => '777777777',
            'adresse' => 'Keur massar',
            'email' => 'cheikhp724@gmail.com',
            'mot_de_passe' => bcrypt('password'),
            'statut' => true,
            'sexe' => 'homme',
            'photo_profile' => 'photo.jpg',
        ]);
        $apprenant->assignRole('Apprenant');
        // $apprenant->promotions()->attach(2);

        $apprenant = User::create([
            'nom' => 'DIOP',
            'prenom' => 'Abdelhamid',
            'matricule' => 'abdelhamid724',
            'telephone' => '788888880',
            'adresse' => 'Meknes',
            'email' => 'abdelhamid724@gmail.com',
            'mot_de_passe' => bcrypt('password'),
            'statut' => true,
            'sexe' => 'homme',
            'photo_profile' => 'photo.jpg',
        ]);
        $apprenant->assignRole('Apprenant');
        // $apprenant->promotions()->attach(3);

$apprenant = User::create([
            'nom' => 'DIALLO',
            'prenom' => 'Alpha',
            'matricule' => 'alp4',
            'telephone' => '788888188',
            'adresse' => 'dakar',
            'email' => 'abdelhamid4@gmail.com',
            'mot_de_passe' => bcrypt('password'),
            'statut' => true,
            'sexe' => 'homme',
            'photo_profile' => 'photo.jpg',
        ]);
        $apprenant->assignRole('Apprenant');

        $apprenant = User::create([
            'nom' => 'Ndiaye',
            'prenom' => 'Alpha',
            'matricule' => 'alfa12',
            'telephone' => '780008888',
            'adresse' => 'dakar',
            'email' => 'alphan@gmail.com',
            'mot_de_passe' => bcrypt('password'),
            'statut' => true,
            'sexe' => 'homme',
            'photo_profile' => 'photo.jpg',
        ]);
        $apprenant->assignRole('Apprenant');
        // $apprenant->promotions()->attach(3);

        $apprenant = User::create([
            'nom' => 'DIOP',
            'prenom' => 'Alpha',
            'matricule' => 'alfa42',
            'telephone' => '780008188',
            'adresse' => 'dakar',
            'email' => 'alph3an@gmail.com',
            'mot_de_passe' => bcrypt('password'),
            'statut' => true,
            'sexe' => 'homme',
            'photo_profile' => 'photo.jpg',
        ]);
        $apprenant->assignRole('Apprenant');
        // $apprenant->promotions()->attach(3);


        $apprenant = User::create([
            'nom' => 'FALL',
            'prenom' => 'OUMY',
            'matricule' => 'fall12',
            'telephone' => '770008288',
            'adresse' => 'dakar',
            'email' => 'fall@gmail.com',
            'mot_de_passe' => bcrypt('password'),
            'statut' => true,
            'sexe' => 'femme',
            'photo_profile' => 'photo.jpg',
        ]);
        $apprenant->assignRole('Apprenant');
        // $apprenant->promotions()->attach(3);
        $apprenant = User::create([
            'nom' => 'FALL',
            'prenom' => 'Abdelhamid',
            'matricule' => 'abdelhamid12',
            'telephone' => '770008388',
            'adresse' => 'dakar',
            'email' => 'abdelhamid@gmail.com',
            'mot_de_passe' => bcrypt('password'),
            'statut' => true,
            'sexe' => 'homme',
            'photo_profile' => 'photo.jpg',
        ]);
        $apprenant->assignRole('Apprenant');
        // $apprenant->promotions()->attach(3);

        $apprenantRole = Role::firstOrCreate(['name' => 'Apprenant']);

         // Génère 20 apprenants via le factory
        User::factory(20)->create()->each(function ($user) use ($apprenantRole) {
            // Assigner le rôle Apprenant
            $user->assignRole($apprenantRole);
        });

    }
}
