<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JuntaDirectiva extends Model
{
    use HasFactory;

    protected $table = 'junta_directiva';
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

    // Scope
    public function scopeActivos($query)
    {
        return $query->where('borrar', false);
    }
}
