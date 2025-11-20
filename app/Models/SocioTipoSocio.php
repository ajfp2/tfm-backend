<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocioTipoSocio extends Model
{
    use HasFactory;

    protected $table = 'socios_tipo_socio';
    protected $primaryKey = 'id_tipo';
    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'descripcion',
        'exentos_pago'
    ];

    protected $casts = [
        'exentos_pago' => 'boolean'
    ];

    // Relaciones
    public function sociosAlta()
    {
        return $this->hasMany(SocioAlta::class, 'fk_tipoSocio', 'id_tipo');
    }

    public function sociosBaja()
    {
        return $this->hasMany(SocioBaja::class, 'fk_tipoSocio', 'id_tipo');
    }

    // Scopes
    public function scopeExentos($query)
    {
        return $query->where('exentos_pago', true);
    }

    public function scopeNonExentos($query)
    {
        return $query->where('exentos_pago', false);
    }
}
