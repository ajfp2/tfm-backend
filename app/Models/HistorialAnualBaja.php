<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialAnualBaja extends Model
{
    use HasFactory;

    protected $table = 'historial_anual_bajas';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = ['a_socio_baja', 'a_temporada'];

    protected $fillable = [
        'a_socio_baja',
        'a_temporada',
        'cuota_pagada',
        'exento',
        'importe_pendiente'
    ];

    protected $casts = [
        'cuota_pagada' => 'boolean',
        'exento' => 'boolean'
    ];

    // Relaciones
    public function socio()
    {
        return $this->belongsTo(SocioPersona::class, 'a_socio_baja', 'Id_Persona');
    }

    public function temporada()
    {
        return $this->belongsTo(Temporada::class, 'a_temporada');
    }

    // Scopes
    public function scopePagados($query)
    {
        return $query->where('cuota_pagada', true);
    }

    public function scopePendientes($query)
    {
        return $query->where('cuota_pagada', false)->where('exento', false);
    }
}
