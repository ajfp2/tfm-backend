<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    use HasFactory;

    protected $table = 'contactos';
    public $timestamps = false;

    protected $fillable = [
        'nom_emp',
        'dni_cif',
        'telefono',
        'fax',
        'email',
        'direccion',
        'cp',
        'poblacion',
        'provincia',
        'pais',
        'contacto',
        'IBAN',
        'BIC'
    ];

    // Relaciones
    public function municipio()
    {
        return $this->belongsTo(SocioMunicipio::class, 'poblacion');
    }

    public function provinciaRelacion()
    {
        return $this->belongsTo(SocioProvincia::class, 'provincia');
    }

    public function paisRelacion()
    {
        return $this->belongsTo(SocioNacionalidad::class, 'pais');
    }
}
