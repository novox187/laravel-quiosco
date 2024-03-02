<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;

class UpdateDespachoController extends Controller
{
    /**
     * Display a listing 
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pedido $pedido)
    {
        $pedido->entregado = 1;
        $pedido->save();

        return [
            'pedido' => $pedido
        ];
    }

}
