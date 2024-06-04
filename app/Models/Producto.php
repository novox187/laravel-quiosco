<?php

namespace App\Models;

use App\Models\Promocione;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Producto extends Model
{
    use HasFactory;

    public function promocion()
    {
        return $this->belongsTo(Promocione::class, 'promo_id');
    }

    public function contenedorOpciones() {
        return $this->belongsToMany(ContenedorOpcione::class)->withTimestamps();;
    }

    public function detallesProductoPedido()
    {
        return $this->hasMany(DetallesProductoPedido::class, 'pedido_producto_id');
    }
    public function categoria()
{
    return $this->belongsTo(Categoria::class);
}
}
