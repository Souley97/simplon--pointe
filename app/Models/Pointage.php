<?php

namespace App\Models;

use App\Models\User;
use App\Models\Justificatif;
use App\Models\ApprenantPromo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pointage extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'type', 'motif', 'date', 'heure_present', 'heure_depart'

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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



}
