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

        //obtenemos el producto eliminado
        $producto = Producto::where('nombre', $request->nombre)->first();

        // Validamos si el producto ya existe o esta eliminado
        if ($producto) {
            if ($producto->eliminado === 1) {

                //Eliminamos la imagen anterior de la base de datos
                Cloudinary::destroy($producto->public_id);

                //Subimos la nueva imagen
                $uploadedFileUrl = Cloudinary::upload($request->imagen->getRealPath(), ['folder' => env('CLOUDINARY_FOLDER_PRODUCTOS'), 'format' => 'avif']);
                $url = $uploadedFileUrl->getSecurePath();
                $public_id = $uploadedFileUrl->getPublicId();

                $producto->eliminado = 0;
                $producto->precio = $datos['precio'];
                $producto->public_id = $public_id;
                $producto->imagen = $url;
                $producto->descripcion = $datos['descripcion'];
                $producto->categoria_id = $datos['categoria'];
                $producto->save();
                return [
                    'data' => $producto,
                    'success' => 'Producto agregado correctamente.',
                ];
            } else {
                $errors = [
                    'campo1' => ['El producto ya existe.'],
                ];
                return response()->json(['errors' => $errors], 422);
            }
        } else {
            $uploadedFileUrl = Cloudinary::upload($request->imagen->getRealPath(), ['folder' => env('CLOUDINARY_FOLDER_PRODUCTOS'), 'format' => 'avif']);
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
                'success' => 'Producto agregado correctamente.'
            ]);
        }
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
            //Eliminamos la imagen anterior de la base de datos
            Cloudinary::destroy($producto->public_id);

            //Subimos la nueva imagen
            $uploadedFileUrl = Cloudinary::upload($request->imagen->getRealPath(), ['folder' => env('CLOUDINARY_FOLDER_PRODUCTOS'), 'format' => 'avif']);
            $url = $uploadedFileUrl->getSecurePath();
            $public_id = $uploadedFileUrl->getPublicId();

            $producto->nombre = $datos['nombre'];
            $producto->precio = $datos['precio'];
            $producto->public_id = $public_id;
            $producto->imagen = $url;
            $producto->descripcion = $datos['descripcion'];
            $producto->promo_id = $request->promo_id;
            $producto->save();
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
        Cloudinary::destroy($producto->public_id);

        $producto->eliminado = 1;
        $producto->categoria_id = null;
        $producto->save();

        return [
            'id' => $producto,
            'message' => 'producto' . ' ' . $producto->nombre . ' ' . 'eliminado'
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
