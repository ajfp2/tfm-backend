<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JuntaDirectiva extends Model
{
    use HasFactory;

    protected $table = 'junta_directiva';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'cargo',
        'borrar'
    ];

    protected $casts = [
        'borrar' => 'boolean'
    ];

    // Relaciones
    public function historialCargos()
    {
        return $this->hasMany(HistorialCargoDirectiva::class, 'a_cargo');
    }

    /**
     * Scope: Solo cargos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', 1);
    }

    // Scope
    public function scopeBorrar($query)
    {
        return $query->where('borrar', true);
    }

    /**
     * Relación: Un cargo puede firmar muchas convocatorias
     */
    public function convocatorias()
    {
        return $this->hasMany(CorrespondenciaJunta::class, 'firma_cargo');
    }

    /**
     * Método: Verificar si existe archivo de firma
     */
    public function tieneFirma()
    {
        $nombreArchivo = 'firma_cargo_' . $this->id . '.png';
        $rutaFirma = storage_path('app/firmas/' . $nombreArchivo);
        return file_exists($rutaFirma);
    }

    /**
     * Método: Obtener ruta de la firma
     */
    public function getRutaFirma()
    {
        $nombreArchivo = 'firma_cargo_' . $this->id . '.png';
        return storage_path('app/firmas/' . $nombreArchivo);
    }
}
