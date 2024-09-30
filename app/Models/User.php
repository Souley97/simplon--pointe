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

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

use Illuminate\Support\Str;

class User extends Authenticatable implements JWTSubject
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
        'password',
        'statut',
        'sexe',
        'photo_profile',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Générer le matricule en combinant le prénom et un numéro aléatoire
            $user->matricule = Str::slug($user->prenom) . '-' . Str::random(5);
        });
    }

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
public function promo()
{
    return $this->belongsTo(Promo::class);
}

public function pointages()
{
    return $this->hasMany(Pointage::class);
}

public function promosForamateur()

{
    return $this->hasMany(Promo::class, 'formateur_id');  // Associe à la clé formateur_id dans la table promos
}
public function promosChefDeProjet()

{
    return $this->hasMany(Promo::class,'chef_projet_id');  // Associe à la clé formateur_id dans la table promos
}
public function justificatifs()
{
    return $this->hasManyThrough(Justificatif::class, Pointage::class, 'user_id', 'pointage_id');
}
// Dans le modèle User
public function promos()
{
    return $this->belongsToMany(Promo::class, 'apprenant_promo');
}
public function getJWTIdentifier()
{
    return $this->getKey();
}

/**
 * Return a key value array, containing any custom claims to be added to the JWT.
 *
 * @return array
 */
public function getJWTCustomClaims()
{
    return [];
}

}
