<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCorrespondencia extends Model
{
    use HasFactory;

    protected $table = 'correspondencia_detallecorrespondencia';
    
    // Clave primaria compuesta
    protected $primaryKey = ['fk_persona', 'fk_correspondencia'];
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fk_correspondencia',
        'fk_persona',
        'papel',
        'nombre',
        'apellidos',
        'direccion',
        'cp',
        'poblacion',
        'provincia',
        'pais',
        'email',
        'fechaenvio',
        'realizado'
    ];

    protected $casts = [
        'papel' => 'boolean',
        'realizado' => 'boolean',
        'fechaenvio' => 'datetime'
    ];

    /**
     * Sobrescribir setKeysForSaveQuery para manejar clave compuesta
     */
    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Obtener el valor de la clave para la query
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }

    /**
     * Relación: Un detalle pertenece a una correspondencia
     */
    public function correspondencia()
    {
        return $this->belongsTo(Correspondencia::class, 'fk_correspondencia');
    }

    /**
     * Relación: Un detalle pertenece a una persona (socio)
     */
    public function persona()
    {
        return $this->belongsTo(SocioPersona::class, 'fk_persona', 'Id_Persona');
    }

    /**
     * Scope: Solo destinatarios por email
     */
    public function scopePorEmail($query)
    {
        return $query->where('papel', 0);
    }

    /**
     * Scope: Solo destinatarios por papel
     */
    public function scopePorPapel($query)
    {
        return $query->where('papel', 1);
    }

    /**
     * Scope: Solo destinatarios con envío realizado
     */
    public function scopeRealizados($query)
    {
        return $query->where('realizado', 1);
    }

    /**
     * Scope: Solo destinatarios pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('realizado', 0);
    }

    /**
     * Accessor: Tipo de envío en texto
     */
    public function getTipoEnvioAttribute()
    {
        return $this->papel ? 'Papel' : 'Email';
    }

    /**
     * Accessor: Estado en texto
     */
    public function getEstadoTextoAttribute()
    {
        if ($this->realizado) {
            return $this->papel ? 'Impreso' : 'Enviado';
        }
        return $this->papel ? 'Para imprimir' : 'Pendiente';
    }

    /**
     * Accessor: Nombre completo
     */
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellidos);
    }

    /**
     * Accessor: Dirección completa
     */
    public function getDireccionCompletaAttribute()
    {
        $partes = array_filter([
            $this->direccion,
            $this->cp ? $this->cp . ' ' . $this->poblacion : $this->poblacion,
            $this->provincia,
            $this->pais
        ]);
        
        return implode(', ', $partes);
    }
}