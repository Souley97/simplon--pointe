<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprenantPromo extends Model
{
    use HasFactory;
    protected $table = 'apprenant_promo'; // Nom de la table de liaison
    public $timestamps = false; // Si tu n'as pas besoin de colonnes `created_at` et `updated_at`

    protected $fillable = [
        'apprenant_id',
        'promo_id',
    ];
}
