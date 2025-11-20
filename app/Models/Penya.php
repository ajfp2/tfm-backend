<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penya extends Model
{
    use HasFactory;

    protected $table = 'penya';
    protected $primaryKey = 'id_penya';
    public $timestamps = false;

    protected $fillable = [
        'cif',
        'nombre',
        'fecha_alta',
        'telefono',
        'direccion',
        'CP',
        'localidad',
        'provincia',
        'email',
        'pwd_email',
        'user_banco',
        'pwd_banco',
        'tarjeta_claves',
        'iban',
        'digitos_control',
        'sufijo',
        'sede_social',
        'direccion_sede',
        'tel_sede',
        'bic'
    ];

    protected $hidden = [
        'pwd_email',
        'pwd_banco',
        'tarjeta_claves'
    ];

    protected $casts = [
        'fecha_alta' => 'datetime'
    ];
}
