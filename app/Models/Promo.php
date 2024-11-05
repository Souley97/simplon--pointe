<?php

namespace App\Models;

use App\Models\User;
use App\Models\Fabrique;
use App\Models\Formation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'date_debut',
        'date_fin',
        'statut',
        'fabrique_id',
        'formateur_id',
        'chef_projet_id',
        'formation_id',
        'horaire'
    ];

    public function fabrique()
    {
        return $this->belongsTo(Fabrique::class);
    }

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function formateur()
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function chefProjet()
    {
        return $this->belongsTo(User::class, 'chef_projet_id');
    }

    public function apprenants()
    {
        return $this->belongsToMany(User::class, 'apprenant_promo');
    }
    
}
