<?php

namespace App\Http\Controllers;

use App\Models\ContenedorOpcione;
use App\Models\Opcione;
use App\Models\Producto;
use Illuminate\Http\Request;
use App\Http\Requests\ProductoRequest;
use App\Http\Resources\ProductoResource;
use App\Http\Requests\ProductoActualizarRequest;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $productos = Producto::with('promocion', 'contenedorOpciones.opciones')
            ->whereHas('categoria', function ($query) {
                $query->where('eliminado', 0);
            })
            ->where('eliminado', 0)
            ->orderBy('disponible', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

        return ProductoResource::collection($productos);
    }

    /**
     * Store a newly created resource in storage.
     */


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
            $producto->peso = $datos['peso'];
            $producto->promo_id = $request->promo_id;
            $producto->save();
        } else {
            //Eliminamos la imagen anterior de la base de datos
            Cloudinary::destroy($producto->public_id);

            //Subimos la nueva imagen
            $uploadedFileUrl = Cloudinary::upload($request->imagen->getRealPath(), ['folder' => 'productos', 'format' => 'avif']);
            $url = $uploadedFileUrl->getSecurePath();
            $public_id = $uploadedFileUrl->getPublicId();

            $producto->nombre = $datos['nombre'];
            $producto->precio = $datos['precio'];
            $producto->public_id = $public_id;
            $producto->imagen = $url;
            $producto->descripcion = $datos['descripcion'];
            $producto->peso = $datos['peso'];
            $producto->promo_id = $request->promo_id;
            $producto->save();
        }

        $productoActualizado = Producto::with('promocion', 'contenedorOpciones.opciones')
            ->where('id', $producto->id)
            ->first();


        return new ProductoResource($productoActualizado);
    }
    public function updateDisponible(Producto $producto)
    {
        if ($producto->disponible === 1) {
            $producto->disponible = 0;
            $producto->save();
        } else {
            $producto->disponible = 1;
            $producto->save();
        }

        return [
            'producto' => $producto->id,
            'estado' => $producto->disponible
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function productoEliminar(Producto $producto)
    {
        Cloudinary::destroy($producto->public_id);

        $producto->eliminado = 1;
        $producto->save();

        return [
            'productoId' => $producto->id,
            'categoriaId' => $producto->categoria_id,
            'message' => 'producto' . ' ' . $producto->nombre . ' ' . 'eliminado'
        ];
    }

    public function cambiarCategoria(Request $request, Producto $producto)
    {
        $producto->categoria_id = $request->id_categoria;
        $producto->save();

        return [
            'productoId' => $producto->id,
            'categoriaAnterior' => $request->categoria_anterior,
            'categoriaActual' => $producto->categoria_id,
            'message' => 'producto' . ' ' . $producto->nombre . ' ' . 'movido a categoria' . ' ' . $request->nombre_categoria,
        ];
    }
}
