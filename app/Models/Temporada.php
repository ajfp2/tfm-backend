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
        'abreviatura',
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
        'saldoIni' => 'decimal:2',
        'activa' => 'boolean',
        'cuotaPasada' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones: Una temporada tiene muchos registros de historial anual (cuotas)1:n
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

    public function scopeOrdenarPorReciente($query)
    {
        return $query->orderBy('fecha_inicio', 'desc');
    }
}
