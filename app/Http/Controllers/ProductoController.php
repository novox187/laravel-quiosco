<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoRequest;
use App\Http\Resources\ProductoCollection;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        /* where('disponible', 1)->orderBy('id', 'DESC')->paginate(10) */
        /* return $request['search']; */
        return new ProductoCollection(Producto::orderBy('disponible', 'DESC')->orderBy('id', 'DESC')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store( ProductoRequest $request)
    {
        $datos = $request->validated();
        
        $imageName = time().'.'.$request->imagen->extension();  
        $nombreLimpio = pathinfo($imageName, PATHINFO_FILENAME);
   
        $request->imagen->move(public_path('img'), $imageName);

        $productoNuevo = new Producto;
        $productoNuevo->nombre = $datos['nombre'];
        $productoNuevo->precio = $datos['precio'];
        $productoNuevo->imagen = $nombreLimpio;
        $productoNuevo->categoria_id = $datos['categoria'];
        $productoNuevo->save();

        return response()->json(['success'=>'Producto guardado correctamente.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Producto $producto)
    {
        if ($producto->disponible === 1) {
            $producto->disponible = 0;
            $producto->save();
        } else {
            $producto->disponible = 1;
            $producto->save();
        }

        return [
            'producto' => $producto
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        //
    }
}
