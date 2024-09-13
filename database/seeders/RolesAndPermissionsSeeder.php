<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        Role::create(['name' => 'Apprenant']);
        Role::create(['name' => 'Formateur']);
        Role::create(['name' => 'Vigile']);
        Role::create(['name' => 'Chef de projet']);
        Role::create(['name' => 'Administrateur']);
    }
}

