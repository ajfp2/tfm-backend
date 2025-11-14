<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'icon',
        'route',
        'order',
        'parent_id',
        'roles',
        'activo'
    ];

    protected $casts = [
        'roles' => 'array',
        'activo' => 'boolean'
    ];

    // Relación: menú padre
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    // Relación: hijos (children)
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    // Scope: menús activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Scope: menús principales (sin padre)
    public function scopePrincipales($query)
    {
        return $query->whereNull('parent_id');
    }

    // Scope: menús por rol
    public function scopePorRol($query, $rol)
    {
        return $query->where(function ($q) use ($rol) {
            $q->whereJsonContains('roles', $rol)
              ->orWhereNull('roles'); // Sin restricción de rol
        });
    }
}
