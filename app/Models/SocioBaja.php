<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocioBaja extends Model
{
    use HasFactory;

    protected $table = 'socios_baja';
    protected $primaryKey = 'a_Persona';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'a_Persona',
        'nsocio',
        'fk_tipoSocio',
        'fecha_alta',
        'fecha_baja',
        'motivo_baja',
        'deudor',
        'deuda',
        'n_carnet',
        'sin_correspondencia',
        'c_carta',
        'c_email',
        'formaPago',
        'fichaMadrid'
    ];

    protected $casts = [
        'fecha_alta' => 'datetime',
        'fecha_baja' => 'datetime',
        'deudor' => 'boolean',
        'deuda' => 'decimal:2',
        'sin_correspondencia' => 'boolean',
        'c_carta' => 'boolean',
        'c_email' => 'boolean',
        'fichaMadrid' => 'boolean'
    ];

    // Relaciones
    public function persona()
    {
        return $this->belongsTo(SocioPersona::class, 'a_Persona', 'Id_Persona');
    }

    public function tipoSocio()
    {
        return $this->belongsTo(SocioTipoSocio::class, 'fk_tipoSocio', 'id_tipo');
    }

    public function formaPago()
    {
        return $this->belongsTo(SocioFormaPago::class, 'formaPago');
    }

    // Scopes
    public function scopeDeudores($query)
    {
        return $query->where('deudor', true);
    }
}
