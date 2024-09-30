<?php

namespace App\Models;

use App\Models\User;
use App\Models\Promo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fabrique extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'localisation',
        'vigile_id'
    ];

    public function promos()
    {
        return $this->hasMany(Promo::class);
    }
    public function vigile()
    {
        return $this->belongsTo(User::class, 'vigile_id');
    }
}
