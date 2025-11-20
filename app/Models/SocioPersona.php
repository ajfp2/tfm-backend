<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocioPersona extends Model
{
    use HasFactory;

    protected $table = 'socios_personas';
    protected $primaryKey = 'Id_Persona';
    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Apellidos',
        'DNI',
        'Movil',
        'Email',
        'Talla',
        'Sexo',
        'FNac',
        'Direccion',
        'CP',
        'Poblacion',
        'Provincia',
        'Pais',
        'Nacionalidad',
        'IBAN',
        'BIC',
        'Iban2',
        'Entidad',
        'Oficina',
        'DC',
        'Cuenta'
    ];

    protected $casts = [
        'FNac' => 'datetime',
        'Movil' => 'string'
    ];

    // Relaciones
    public function alta()
    {
        return $this->hasOne(SocioAlta::class, 'a_Persona', 'Id_Persona');
    }

    public function baja()
    {
        return $this->hasOne(SocioBaja::class, 'a_Persona', 'Id_Persona');
    }

    public function municipio()
    {
        return $this->belongsTo(SocioMunicipio::class, 'Poblacion');
    }

    public function provincia()
    {
        return $this->belongsTo(SocioProvincia::class, 'Provincia');
    }

    public function pais()
    {
        return $this->belongsTo(SocioNacionalidad::class, 'Pais');
    }

    public function nacionalidad()
    {
        return $this->belongsTo(SocioNacionalidad::class, 'Nacionalidad');
    }

    public function historialAnual()
    {
        return $this->hasMany(HistorialAnual::class, 'a_socio', 'Id_Persona');
    }

    public function historialAnualBajas()
    {
        return $this->hasMany(HistorialAnualBaja::class, 'a_socio_baja', 'Id_Persona');
    }

    public function cargosDirectivos()
    {
        return $this->hasMany(HistorialCargoDirectiva::class, 'a_persona', 'Id_Persona');
    }

    // Métodos auxiliares
    public function getNombreCompletoAttribute()
    {
        return "{$this->Apellidos}, {$this->Nombre}";
    }

    public function isActivo()
    {
        return $this->alta()->exists();
    }

    public function isBaja()
    {
        return $this->baja()->exists();
    }
}
