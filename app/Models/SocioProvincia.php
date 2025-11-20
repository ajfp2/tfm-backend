<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocioProvincia extends Model
{
    use HasFactory;

    protected $table = 'socios_provincias';
    public $timestamps = false;

    protected $fillable = [
        'provincia',
        'pais'
    ];

    // Relaciones
    public function municipios()
    {
        return $this->hasMany(SocioMunicipio::class, 'provincia');
    }

    public function personas()
    {
        return $this->hasMany(SocioPersona::class, 'Provincia');
    }
}
