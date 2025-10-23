<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Socio extends Model
{
    protected $table = 'socios_personas';

    protected $primaryKey = 'Id_Persona'; // clave primaria

    public $incrementing = true; // si es autoincremental
    protected $keyType = 'int'; // tipo de clave

    public $timestamps = false; // si no tienes created_at y updated_at

    protected $fillable = [
        'Nombre',
        'Apellidos',
        'DNI',
        'Movil',
        'Email',
        'Sexo',
        'FNac',
        'Direccion',
        'CP',
        'Poblacion',
        'Provincia',
        'Pais',
        'Nacionalidad'
    ];
}
