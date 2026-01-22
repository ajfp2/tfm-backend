<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrespondenciaJunta extends Model
{
    use HasFactory;

    protected $table = 'correspondencia_juntas';
    protected $primaryKey = 'id';
    public $timestamps = false; // La tabla no usa created_at/updated_at

    protected $fillable = [
        'convocatoria',
        'fecha',
        'fecha_junta',
        'hora1',
        'hora2',
        'lugar',
        'asunto',
        'firma_cargo',
        'vb_presidente',
        'texto',
        'fecha_envio',
        'estado',
        'pdfgenerado',
        'fk_temporadas'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'fecha_junta' => 'date',
        'fecha_envio' => 'date',
        'vb_presidente' => 'boolean',
        'estado' => 'boolean',
        'pdfgenerado' => 'boolean'
    ];

    /**
     * Relación: Una convocatoria pertenece a una temporada
     */
    public function temporada()
    {
        return $this->belongsTo(Temporada::class, 'fk_temporadas');
    }

    /**
     * Relación: Una convocatoria puede tener muchas correspondencias vinculadas
     */
    public function correspondencias()
    {
        return $this->hasMany(Correspondencia::class, 'fk_convocatoria');
    }

    public function cargoFirmante()
    {
        // Ajusta 'Cargo' al nombre real de tu modelo relacionado
        return $this->belongsTo(JuntaDirectiva::class, 'firma_cargo'); 
    }

    /**
     * Scope: Solo convocatorias sin enviar
     */
    public function scopeSinEnviar($query)
    {
        return $query->where('estado', 0);
    }

    /**
     * Scope: Solo convocatorias enviadas
     */
    public function scopeEnviadas($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope: Solo convocatorias con PDF generado
     */
    public function scopeConPdf($query)
    {
        return $query->where('pdfgenerado', 1);
    }

    /**
     * Scope: Solo convocatorias con VºBº
     */
    public function scopeConVoBo($query)
    {
        return $query->where('vb_presidente', 1);
    }

    /**
     * Scope: Ordenar por fecha de junta
     */
    public function scopeOrdenarPorFecha($query, $direccion = 'desc')
    {
        return $query->orderBy('fecha_junta', $direccion);
    }

    /**
     * Accessor: Estado en texto
     */
    public function getEstadoTextoAttribute()
    {
        return $this->estado ? 'Enviada' : 'Sin enviar';
    }

    /**
     * Accessor: PDF en texto
     */
    public function getPdfTextoAttribute()
    {
        return $this->pdfgenerado ? 'PDF Generado' : 'Borrador';
    }

    /**
     * Accessor: VºBº en texto
     */
    public function getVoBoTextoAttribute()
    {
        return $this->vb_presidente ? 'Con VºBº' : 'Sin VºBº';
    }
}
