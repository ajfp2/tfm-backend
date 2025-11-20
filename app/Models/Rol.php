<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = "roles";

    public function user(){
        return $this->hasMany(User::class);
    }

    /***************************
    PARA RELACIONES DE TABLAS N:N con tabla intermedia
    ******************************/

    // use Illuminate\Database\Eloquent\Relations\BelongsToMany;

    // public function categories(): BelongsToMany{
    //     return $this->belongsToMany(Category::class);
    // }

    // public function recipes(): BelongsToMany{
    //     return $this->belongsToMany(Recipe::class);
    // }

    
}
