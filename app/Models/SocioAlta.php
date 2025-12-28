<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocioAlta extends Model
{
    use HasFactory;

    protected $table = 'socios_alta';
    protected $primaryKey = 'a_Persona';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'a_Persona',
        'nsocio',
        'fk_tipoSocio',
        'fecha_alta',
        'n_carnet',
        'sin_correspondencia',
        'c_carta',
        'c_email',
        'formaPago',
        'fichaMadrid'
    ];

    protected $casts = [
        'fecha_alta' => 'datetime',
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
    public function scopePagoBanco($query)
    {
        return $query->where('formaPago', 1);
    }

    public function scopePagoEfectivo($query)
    {
        return $query->where('formaPago', 2);
    }

    public function scopeExentos($query)
    {
        return $query->whereHas('tipoSocio', function($q) {
            $q->where('exentos_pago', 1);
        });
    }
}
