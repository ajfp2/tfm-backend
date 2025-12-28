<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penya extends Model
{
    use HasFactory;

    protected $table = 'penya';
    protected $primaryKey = 'id_penya';
    public $timestamps = true;

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
        'pwd_banco'
    ];

    protected $casts = [
        'fecha_alta' => 'datetime'
    ];

    public static function getInstance()
    {
        $penya = self::select('id_penya','cif', 'nombre', 'fecha_alta', 'telefono', 'direccion', 'CP', 'localidad', 'provincia', 'email', 'pwd_email',
        'sede_social', 'direccion_sede', 'tel_sede')->get();
        
        if (!$penya) {
            // Si no existe, crear una por defecto
            $penya = self::create([
                'nombre' => 'Mi Peña',
                'cif' => '',
                'direccion' => '',
                'CP' => '',
                'localidad' => '',
                'provincia' => '',
                'telefono' => '',
                'email' => '',
            ]);
        }
        
        return $penya;
    }

    public static function getInstanceBank()
    {
        $penya = self::select('nombre_banco', 'user_banco','pwd_banco', 'tarjeta_claves', 'iban', 'digitos_control', 'sufijo', 'bic')->get();
        
        if (!$penya) {
            return null;            
        }
        
        return $penya;
    }

    public function validarIban(): bool
    {
        if (empty($this->iban)) {
            return false;
        }

        // Eliminar espacios
        $iban = str_replace(' ', '', $this->iban);

        // Mover los 4 primeros caracteres al final
        $ibanReordenado = substr($iban, 4) . substr($iban, 0, 4);

        // Convertir letras a números (A=10, B=11, ..., Z=35)
        $ibanNumerico = '';
        for ($i = 0; $i < strlen($ibanReordenado); $i++) {
            $char = $ibanReordenado[$i];
            if (ctype_alpha($char)) {
                $ibanNumerico .= (ord(strtoupper($char)) - 55);
            } else {
                $ibanNumerico .= $char;
            }
        }

        // Calcular módulo 97
        return bcmod($ibanNumerico, '97') === '1';
    }
}
