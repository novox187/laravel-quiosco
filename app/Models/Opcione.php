<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opcione extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'icono', 'precio','public_id', 'contenedor_id']; // Agregar 'nombre' al array fillable
    public function contenedor()
    {
        return $this->belongsTo(ContenedorOpcione::class, 'contenedor_id', 'id');
    }
}
