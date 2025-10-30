<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuracion';


    protected $primaryKey = 'id';

    
    public $timestamps = false;


    protected $fillable = [
        'tipo',
        'ejercicio',
        'modificado'
    ];

    protected $casts = [
        'modificado' => 'boolean',
    ];

    protected $attributes = [
        'modificado' => false,
    ];
}
