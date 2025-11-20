<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocioFormaPago extends Model
{
    use HasFactory;

    protected $table = 'socios_forma_pago';
    public $timestamps = false;

    protected $fillable = [
        'forma'
    ];

    // Relaciones
    public function sociosAlta()
    {
        return $this->hasMany(SocioAlta::class, 'formaPago');
    }

    public function sociosBaja()
    {
        return $this->hasMany(SocioBaja::class, 'formaPago');
    }
}
