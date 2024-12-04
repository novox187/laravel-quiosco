<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallesEntrega extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'favorito',
        'distancia_km',
        'distancia_fuera_radio',
        'precio_total',
        'telefono',
        'comentario',
        'direccion_mapa',
        'latitud',
        'longitud',
        'created_at',
        'updated_at',
    ];    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'detalle_entrega_id');
    }
}
