<?php

namespace App\Models;

use App\Models\Pointage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Justificatif extends Model
{
    use HasFactory;
    protected $fillable = [
        'date',
        'description',
        'document',
        'pointage_id',
    ];

    public function pointage()
    {
        return $this->belongsTo(Pointage::class);
    }
}
