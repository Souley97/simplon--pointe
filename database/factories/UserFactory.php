<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'nom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),
            'matricule' => $this->faker->unique()->userName(),
            'telephone' => $this->faker->phoneNumber(),
            'adresse' => $this->faker->address(),
            'email' => $this->faker->unique()->safeEmail(),
            'mot_de_passe' => Hash::make('password'), // ou bcrypt('password')
            'statut' => true,
            'sexe' => $this->faker->randomElement(['homme', 'femme']),
            'photo_profile' => 'default.jpg', // Une image par défaut
        ];
    }
}
