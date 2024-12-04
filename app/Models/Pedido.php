<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'pedido_productos');
    }
    public function pedidoProductos()
    {
        return $this->hasMany(PedidoProducto::class);
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function detalleEntrega()
    {
        return $this->belongsTo(DetallesEntrega::class, 'detalle_entrega_id');
    }
}
