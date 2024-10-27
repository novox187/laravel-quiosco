<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cierres_caja extends Model
{
    use HasFactory;

    protected $table = 'cierres_caja';

    protected $fillable = [
        'id_caja',
        'id_apertura',
        'monto_final',
        'total_ventas',
        'discrepancia',
        'usuario_cierre',
        'usuario_modificacion',
    ];

    // Relación con Caja
    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }

    // Relación con AperturaCaja
    public function apertura()
    {
        return $this->belongsTo(Aperturas_caja::class, 'id_apertura');
    }
}
