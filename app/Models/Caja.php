<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    protected $table = 'cajas';

    protected $fillable = [
        'nombre_caja',
        'estado',
    ];

    // Relación con AperturasCaja
    public function aperturas()
    {
        return $this->hasMany(Aperturas_caja::class, 'id_caja');
    }

    // Relación con CierresCaja
    public function cierres()
    {
        return $this->hasMany(Cierres_caja::class, 'id_caja');
    }

    // Relación con Transacciones
    public function transacciones()
    {
        return $this->hasMany(Transacciones::class, 'id_caja');
    }
}
