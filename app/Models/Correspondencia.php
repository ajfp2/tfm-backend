<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Correspondencia extends Model
{
    use HasFactory;

    protected $table = 'correspondencia_correspondencia';
    protected $primaryKey = 'id';
    public $timestamps = false; // La tabla no usa created_at/updated_at estándar

    protected $fillable = [
        'descripcion',
        'asunto',
        'texto',
        'creado',
        'diaenvio',
        'rutafichero',
        'firma_cargo',
        'vb_presidente',
        'estadofinalizado',
        'fk_convocatoria',
        'fk_temporadas'
    ];

    protected $casts = [
        'creado' => 'datetime',
        'diaenvio' => 'datetime',
        'estadofinalizado' => 'boolean',
        'vb_presidente' => 'boolean'
    ];

    /**
     * Relación: Una correspondencia pertenece a una temporada
     */
    public function temporada()
    {
        return $this->belongsTo(Temporada::class, 'fk_temporadas');
    }

    /**
     * Relación: Una correspondencia puede estar vinculada a una convocatoria
     */
    public function convocatoria()
    {
        return $this->belongsTo(CorrespondenciaJunta::class, 'fk_convocatoria');
    }

    /**
     * Relación: Una correspondencia tiene un cargo firmante
     */
    public function cargoFirmante()
    {
        return $this->belongsTo(JuntaDirectiva::class, 'firma_cargo', 'id');
    }

    /**
     * Relación: Una correspondencia tiene muchos destinatarios
     */
    public function destinatarios()
    {
        return $this->hasMany(DetalleCorrespondencia::class, 'fk_correspondencia');
    }

    /**
     * Scope: Solo correspondencia no enviada
     */
    public function scopePendientes($query)
    {
        return $query->where('estadofinalizado', 0);
    }

    /**
     * Scope: Solo correspondencia enviada
     */
    public function scopeEnviadas($query)
    {
        return $query->where('estadofinalizado', 1);
    }

    /**
     * Scope: Ordenar por fecha de creación
     */
    public function scopeRecientes($query)
    {
        return $query->orderBy('creado', 'desc');
    }

    /**
     * Scope: Con convocatoria vinculada
     */
    public function scopeConConvocatoria($query)
    {
        return $query->whereNotNull('fk_convocatoria');
    }

    /**
     * Scope: Sin convocatoria (independiente)
     */
    public function scopeIndependiente($query)
    {
        return $query->whereNull('fk_convocatoria');
    }

    /**
     * Accessor: Estado en texto
     */
    public function getEstadoTextoAttribute()
    {
        return $this->estadofinalizado ? 'Enviada' : 'Pendiente';
    }

    /**
     * Método: Contar destinatarios por email
     */
    public function totalEmail()
    {
        return $this->destinatarios()->where('papel', 0)->count();
    }

    /**
     * Método: Contar destinatarios por papel
     */
    public function totalPapel()
    {
        return $this->destinatarios()->where('papel', 1)->count();
    }

    /**
     * Método: Contar destinatarios con envío realizado
     */
    public function totalRealizados()
    {
        return $this->destinatarios()->where('realizado', 1)->count();
    }

    /**
     * Método: Contar destinatarios pendientes
     */
    public function totalPendientes()
    {
        return $this->destinatarios()->where('realizado', 0)->count();
    }
}