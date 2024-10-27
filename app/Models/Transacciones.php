<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transacciones extends Model
{
    use HasFactory;

    protected $table = 'transacciones';

    protected $fillable = [
        'id_caja',
        'id_apertura',
        'id_pedido',
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

    // Relación con Pedido
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }
}
