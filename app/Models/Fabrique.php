<?php

namespace App\Models;

use App\Models\Promo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fabrique extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'localisation',
    ];

    public function promos()
    {
        return $this->hasMany(Promo::class);
    }
}
