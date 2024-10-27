<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aperturas_caja extends Model
{
    use HasFactory;
    protected $table = 'aperturas_caja';

    protected $fillable = [
        'id_caja',
        'monto_inicial',
        'usuario_apertura',
        'usuario_modificacion',
    ];

    // Relación con Caja
    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }

    // Relación con Transacciones
    public function transacciones()
    {
        return $this->hasMany(Transacciones::class, 'id_apertura');
    }

    // Relación con CierresCaja
    public function cierre()
    {
        return $this->hasOne(Cierres_caja::class, 'id_apertura');
    }
}
