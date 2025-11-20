<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Temporada extends Model
{
    use HasFactory;

    protected $table = 'temporadas';
    public $timestamps = false;

    protected $fillable = [
        'temporada',
        'abrev',
        'fechaIni',
        'fechaFin',
        'saldoIni',
        'saldoFin',
        'activa',
        'cuotaPasada'
    ];

    protected $casts = [
        'fechaIni' => 'datetime',
        'fechaFin' => 'datetime',
        'activa' => 'boolean',
        'cuotaPasada' => 'boolean'
    ];

    // Relaciones
    public function historialAnual()
    {
        return $this->hasMany(HistorialAnual::class, 'a_temporada');
    }

    public function historialAnualBajas()
    {
        return $this->hasMany(HistorialAnualBaja::class, 'a_temporada');
    }

    public function cargosDirectivos()
    {
        return $this->hasMany(HistorialCargoDirectiva::class, 'a_temporada');
    }

    // Scopes
    public function scopeActiva($query)
    {
        return $query->where('activa', true);
    }
}
