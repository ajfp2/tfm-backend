<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TareaPendiente extends Model
{
    use HasFactory;

    protected $table = 'tareas_pendientes';
    public $timestamps = false;

    protected $fillable = [
        'menu',
        'descripcion',
        'estado',
        'rutamenu',
        'progreso',
        'finalizado'
    ];

    protected $casts = [
        'progreso' => 'integer',
        'finalizado' => 'boolean'
    ];

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('finalizado', false);
    }

    public function scopeFinalizadas($query)
    {
        return $query->where('finalizado', true);
    }
}
