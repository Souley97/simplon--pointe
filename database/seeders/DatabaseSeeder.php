<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(ApprenantPromoTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(FormationsTableSeeder::class);
        $this->call(FabriquesTableSeeder::class);
        $this->call(PromosTableSeeder::class);

        // $this->call(PointagesTableSeeder::class);
        $this->call(JustificatifsTableSeeder::class);



    }
}
