<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compteur extends Model
{
    protected $primaryKey = 'id';
    protected $fillable = [
        'numero_contrat',
        'numero_compteur',
        'address',
        'type',
        'date_creation',
    ];
    public function consommations()
    {
        return $this->hasMany(Consommation::class, 'numero_contrat', 'numero_contrat');
    }
}
