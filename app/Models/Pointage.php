<?php

namespace App\Models;

use App\Models\User;
use App\Models\Promo;
use App\Models\Justificatif;
use App\Models\ApprenantPromo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pointage extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'type', 'motif', 'date', 'heure_present', 'heure_depart', 'created_by'

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function promo()
{
    return $this->belongsTo(Promo::class);
}


    public function justificatifs()
    {
        return $this->hasMany(Justificatif::class);
    }
    // apprenantPromo
    public function apprenantPromo()
    {
        return $this->belongsTo(ApprenantPromo::class, 'user_id'); // Utilise l'identifiant correct
    }

    // created by
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by'); // Utilise l'identifiant correct
    }




}
