<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialAnual extends Model
{
    use HasFactory;

    protected $table = 'historial_anual';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = ['a_socio', 'a_temporada'];

    protected $fillable = [
        'a_socio',
        'a_temporada',
        'cuota_pagada',
        'exento',
        'importe',
        'importe_pendiente'
    ];

    protected $casts = [
        'cuota_pagada' => 'boolean',
        'exento' => 'boolean',
        'importe' => 'decimal:2',
        'importe_pendiente' => 'decimal:2'
    ];

    // Relaciones
    public function socio()
    {
        return $this->belongsTo(SocioPersona::class, 'a_socio', 'Id_Persona');
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

    public function scopeExentos($query)
    {
        return $query->where('exento', true);
    }

    public function estaPagado()
    {
        return $this->cuota_pagada == 1;
    }

    public function esDeudor()
    {
        return !$this->exento && 
               !$this->cuota_pagada && 
               $this->importe_pendiente > 0;
    }

    public function marcarComoPagado()
    {
        $this->cuota_pagada = 1;
        $this->importe_pendiente = 0;
        $this->save();
    }
}
