<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuracion';


    protected $primaryKey = 'id';

    
    public $timestamps = true;


    protected $fillable = [
        'tipo',
        'ejercicio',
        'modificado',
        'titulo',
        'subtitulo',
        'logo',
        'navbar_color',
        'gradient_from',
        'gradient_to',
        'a_temporada_activa'
    ];

    protected $casts = [
        'modificado' => 'boolean',
    ];

    protected $attributes = [
        'modificado' => false,
    ];

    /**
     * Obtener la única instancia de configuración (Singleton)
     */
    public static function getInstance()
    {
        $config = self::first();
        
        if (!$config) {
            // Si no existe, crear una por defecto
            $config = self::create([
                'tipo' => 'Peña',
                'ejercicio' => 'Temporada',
                'modificado' => false,
                'titulo' => 'Sistema de Gestión',
                'subtitulo' => 'Temporada',
                'logo' => '',
                'navbar_color' => '#0d6efd',
                'gradient_from' => '#667eea',
                'gradient_to' => '#764ba2',
            ]);
        }
        
        return $config;
    }

    /**
     * Relación: Pertenece a una Temporada activa
     */
    public function temporadaActiva()
    {
        return $this->belongsTo(Temporada::class, 'a_temporada_activa', 'id');
    }
}
