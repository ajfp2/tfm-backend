<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocioMunicipio extends Model
{
    use HasFactory;

    protected $table = 'socios_municipios';
    public $timestamps = false;

    protected $fillable = [
        'provincia',
        'municipio'
    ];

    // Relaciones
    public function provincia()
    {
        return $this->belongsTo(SocioProvincia::class, 'provincia');
    }

    public function personas()
    {
        return $this->hasMany(SocioPersona::class, 'Poblacion');
    }
}
