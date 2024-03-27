<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use App\Http\Requests\ProductoRequest;
use App\Http\Resources\ProductoResource;
use App\Http\Resources\ProductoCollection;
use App\Http\Requests\ProductoActualizarRequest;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $productos = Producto::with('promocion')
            ->where('eliminado', 0)
            ->orderBy('disponible', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();
        return ProductoResource::collection($productos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductoRequest $request)
    {
        $datos = $request->validated();

        $uploadedFileUrl = Cloudinary::upload($request->imagen->getRealPath(), ['folder' => 'productos','format'=>'avif']);
        $url = $uploadedFileUrl->getSecurePath();
        $public_id = $uploadedFileUrl->getPublicId();

        $productoNuevo = new Producto;
        $productoNuevo->nombre = $datos['nombre'];
        $productoNuevo->precio = $datos['precio'];
        $productoNuevo->public_id = $public_id;
        $productoNuevo->imagen = $url;
        $productoNuevo->descripcion = $datos['descripcion'];
        $productoNuevo->categoria_id = $datos['categoria'];
        $productoNuevo->save();

        return response()->json([
            'data' => $productoNuevo,
            'success' => 'Producto guardado correctamente.'
        ]);
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
            $producto->promo_id = $request->promo_id;
            $producto->save();
        } else {
            //Agrega un nombre y con su extencion
            $imageName = time() . '.' . $request->imagen->extension();

            $producto->nombre = $datos['nombre'];
            $producto->precio = $datos['precio'];
            $producto->imagen = $imageName;
            $producto->descripcion = $datos['descripcion'];
            $producto->promo_id = $request->promo_id;
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
    public function productoEliminar(Producto $producto)
    {
        $producto->eliminado = 1;
        $producto->categoria_id = null;
        $producto->save();

        return [
            'id' => $producto,
            'message' => 'producto'.' '. $producto->nombre.' '.'eliminado'
        ];
    }

    public function cambiarCategoria(Request $request, Producto $producto)
    {
        $producto->categoria_id = $request->id_categoria;
        $producto->save();

        return [
            'data' => $producto,
            'message' => 'producto' . ' ' . $producto->nombre . ' ' . 'movido satisfactoriamente a categoria' . ' ' . $request->nombre_categoria,
        ];
    }
}
