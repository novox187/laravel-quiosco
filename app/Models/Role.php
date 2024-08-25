<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'rol',
        'eliminar',
        'editar',
        'ver',
        'preparar_pedidos',
        'entregar_pedidos',
    ];

    /**
     * The employees that belong to the role.
     */
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_roles');
    }
}
