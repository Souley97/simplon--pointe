<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Promo;
use App\Models\Pointage;
use App\Models\Justificatif;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasRoles;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'prenom',
        'matricule',
        'telephone',
        'adresse',
        'email',
        'mot_de_passe',
        'statut',
        'sexe',
        'photo_profile',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function promotions()
{
    return $this->belongsToMany(Promo::class, 'apprenant_promo');
}

public function pointages()
{
    return $this->hasMany(Pointage::class);
}

public function justificatifs()
{
    return $this->hasManyThrough(Justificatif::class, Pointage::class, 'user_id', 'pointage_id');
}
// Dans le modèle User
public function promos()
{
    return $this->belongsToMany(Promo::class, 'apprenant_promo'); // Si la table pivot est apprenant_promo
}

}
