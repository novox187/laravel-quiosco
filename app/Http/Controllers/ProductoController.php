<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoActualizarRequest;
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
    public function store(ProductoRequest $request)
    {
        $datos = $request->validated();


        //Agrega un nombre y con su extencion
        $imageName = time() . '.' . $request->imagen->extension();
        /*   $nombreLimpio = pathinfo($imageName, PATHINFO_FILENAME); */

        $productoNuevo = new Producto;
        $productoNuevo->nombre = $datos['nombre'];
        $productoNuevo->precio = $datos['precio'];
        $productoNuevo->imagen = $imageName;
        $productoNuevo->descripcion = $datos['descripcion'];
        $productoNuevo->categoria_id = $datos['categoria'];
        $productoNuevo->save();


        //mueve la imagen a la carpeta public/img
        $request->imagen->move(public_path('img'), $imageName);

        return response()->json(['success' => 'Producto guardado correctamente.']);
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
    public function productoActualizar(ProductoActualizarRequest $request, Producto $producto)
    {
        $datos = $request->validated();

        if (empty($request->imagen)) {
            $producto->nombre = $datos['nombre'];
            $producto->precio = $datos['precio'];
            $producto->descripcion = $datos['descripcion'];
            $producto->save();
        } else{
            //Agrega un nombre y con su extencion
            $imageName = time() . '.' . $request->imagen->extension();
            
            $producto->nombre = $datos['nombre'];
            $producto->precio = $datos['precio'];
            $producto->imagen = $imageName;
            $producto->descripcion = $datos['descripcion'];
            $producto->save();

            //mueve la imagen a la carpeta public/img
            $request->imagen->move(public_path('img'), $imageName);
        }

        return [
            'producto' => $request->nombre
        ];
    }
    public function updateDisponible(Request $request, Producto $producto)
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
