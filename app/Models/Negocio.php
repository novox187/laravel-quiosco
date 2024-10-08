<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Negocio extends Model
{
    use HasFactory;

    protected $fillable = [
        'logo',
        'nombre',
        'eslogan',
        'direccion',
        'telefono',
        'email',
        'ruc',
    ];
}
