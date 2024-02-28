<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Pedido;
use Illuminate\Http\Request;
use App\Models\PedidoProducto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PedidoCollection;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pedidos = Pedido::with('user')
            ->with('productos')
            ->where('estado', 0)
            ->get();


        $productos = DB::table('pedido_productos')
            ->select('producto_id', DB::raw('SUM(cantidad) as total_vendido'))
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->limit(3)
            ->pluck('producto_id'); // Obtener solo los IDs de los productos más vendidos

            $productosMasVendidos = DB::table('productos')
                ->whereIn('id', $productos)
                ->get();
        return [
            'pedidos' => new PedidoCollection($pedidos),
            'productos_mas_vendidos' => $productosMasVendidos,
        ];

        /* return new PedidoCollection(Pedido::with('user')->with('productos')->where('estado', 0)->get()); */
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Almacenar orden
        $pedido = new Pedido;
        $pedido->user_id = Auth::user()->id;
        $pedido->total = $request->total;
        $pedido->save();

        // Obtener el ID del pedido
        $id = $pedido->id;

        // Obtener los productos
        $productos = $request->productos;

        // Formatear un arreglo
        $pediddo_producto = [];

        foreach ($productos as $producto) {
            $pediddo_producto[] = [
                'pedido_id' => $id,
                'producto_id' => $producto['id'],
                'cantidad' => $producto['cantidad'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }

        // Almacenar en la BD
        PedidoProducto::insert($pediddo_producto);

        return [
            'message' => 'Pedido realizado Correctamente, estara listo en usnos minutos'
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(Pedido $pedido)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pedido $pedido)
    {
        $pedido->estado = 1;
        $pedido->save();

        return [
            'pedido' => $pedido
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pedido $pedido)
    {
        //
    }
}
