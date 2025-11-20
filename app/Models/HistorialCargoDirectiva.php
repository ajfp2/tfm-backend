<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialCargoDirectiva extends Model
{
    use HasFactory;

    protected $table = 'historial_cargos_directivos';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'a_temporada',
        'a_persona',
        'a_cargo'
    ];

    // Relaciones
    public function temporada()
    {
        return $this->belongsTo(Temporada::class, 'a_temporada');
    }

    public function persona()
    {
        return $this->belongsTo(SocioPersona::class, 'a_persona', 'Id_Persona');
    }

    public function cargo()
    {
        return $this->belongsTo(JuntaDirectiva::class, 'a_cargo');
    }
}
