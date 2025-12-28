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
        'BIC'
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

    // Scope: Socios activos (de alta)
    public function scopeActivos($query)
    {
        return $query->whereHas('alta');
    }

    // Scope: Socios de baja
    public function scopeBajas($query)
    {
        return $query->whereHas('baja');
    }

    // Scope: Socios exentos (tipo de socio con exento = 1)
    public function scopeExentos($query)
    {
        return $query->whereHas('alta.tipoSocio', function($q) {
            $q->where('exentos_pago', 1);
        });
    }

    // ==========================================
    // MÉTODOS SOCIOS EXENTOS
    // ==========================================

    // Obtener todos los socios exentos de alta
    public static function getSociosExentos()
    {
        return self::with(['alta.tipoSocio'])
            ->activos()
            ->exentos()
            ->get();
    }

    // Obtener socios exentos simple (para dropdowns)
    public static function getSociosExentosSimple()
    {
        return self::with('alta')
            ->activos()
            ->exentos()
            ->get()
            ->map(function($persona) {
                return [
                    'Id_Persona' => $persona->Id_Persona,
                    'nsocio' => $persona->alta->n_socio ?? null,
                    'nombre_completo' => $persona->Apellidos . ', ' . $persona->Nombre,
                ];
            });
    }

    // Contar socios exentos
    public static function contarSociosExentos(){
        return self::activos()->exentos()->count();
    }

    // Obtener estadísticas de socios exentos
    public static function getEstadisticasExentos()
    {
        $totalSocios = self::activos()->count();
        $totalExentos = self::contarSociosExentos();
        $totalNoExentos = $totalSocios - $totalExentos;
        $porcentajeExentos = $totalSocios > 0 ? round(($totalExentos / $totalSocios) * 100, 2) : 0;

        // Agrupar por tipo de socio
        $exentosPorTipo = self::with(['alta.tipoSocio'])
                            ->activos()
                            ->exentos()
                            ->get()
                            ->groupBy('alta.tipoSocio.tipo')
                            ->map(function($grupo) {
                                $tipoSocio = $grupo->first()->alta->tipoSocio;
                                return [
                                    'tipo' => $tipoSocio->tipo ?? 'Sin tipo',
                                    'cantidad' => $grupo->count(),
                                    'importe' => $tipoSocio->importe ?? 0
                                ];
                            })
                            ->values();

        return [
            'total_socios' => $totalSocios,
            'total_exentos' => $totalExentos,
            'total_no_exentos' => $totalNoExentos,
            'porcentaje_exentos' => $porcentajeExentos,
            'exentos_por_tipo' => $exentosPorTipo
        ];
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

    public function estaExento()
    {
        if (!$this->isActivo()) {
            return false;
        }

        return $this->alta && 
               $this->alta->tipoSocio && 
               $this->alta->tipoSocio->exento == 1;
    }

    public function getTipoSocio()
    {
        return $this->alta?->tipoSocio;
    }

}
