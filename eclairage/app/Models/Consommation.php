<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consommation extends Model
{
    protected $table = 'consommation';

    protected $fillable = [
        'numero_contrat',
        'type',
        'c_kwh',
        'c_dhs',
        'periode',
    ];

    public function compteur()
    {
        return $this->belongsTo(Compteur::class, 'numero_contrat', 'numero_contrat');
    }
}
