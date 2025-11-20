<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocioNacionalidad extends Model
{
    use HasFactory;

    protected $table = 'socios_nacionalidad';
    public $timestamps = false;

    protected $fillable = [
        'pais',
        'nacionalidad',
        'codigo'
    ];

    // Relaciones
    public function personasPais()
    {
        return $this->hasMany(SocioPersona::class, 'Pais');
    }

    public function personasNacionalidad()
    {
        return $this->hasMany(SocioPersona::class, 'Nacionalidad');
    }
}
