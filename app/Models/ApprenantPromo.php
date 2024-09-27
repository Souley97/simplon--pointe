<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprenantPromo extends Model
{
    use HasFactory;
    protected $table = 'apprenant_promo'; // Nom de la table de liaison
    public $timestamps = false;

    protected $fillable = [
        'user_id', // Utiliser 'user_id' au lieu de 'apprenant_id'
        'promo_id',
    ];

    public function pointages()
{
    return $this->hasMany(Pointage::class, 'apprenant_id');
}
    
}
