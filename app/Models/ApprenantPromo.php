<?php

namespace App\Models;

use App\Models\User;
use App\Models\Promo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

// Relation avec les promotions
public function promo()
{
    return $this->belongsTo(Promo::class, 'promo_id');
}
}
