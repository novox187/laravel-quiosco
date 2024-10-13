<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContenedorOpcione extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'image', 'public_id', 'tipo'];

    public function opciones()
    {
        return $this->hasMany(Opcione::class, 'contenedor_id', 'id');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'contenedor_opcione_producto', 'contenedor_opcione_id', 'producto_id')->withTimestamps();
    }
}