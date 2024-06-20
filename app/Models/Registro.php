<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    use HasFactory;


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
