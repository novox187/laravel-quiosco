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
                $uploadedFileUrl = Cloudinary::upload($request->imagen->getRealPath(), ['folder' => 'desarrollo/productos', 'format' => 'avif']);
                $url = $uploadedFileUrl->getSecurePath();
                $public_id = $uploadedFileUrl->getPublicId();

                $producto->eliminado = 0;
                $producto->precio = $datos['precio'];
                $producto->public_id = $public_id;
                $producto->imagen = $url;
                $producto->descripcion = $datos['descripcion'];
                $producto->categoria_id = $datos['categoria'];
                $producto->save();

                $opcionesProducto = $request->opciones_producto;
                $contenedoresIds = [];

                if ($opcionesProducto) {
                    foreach ($opcionesProducto as $opcion) {

                        $Confirmarcontenedor = ContenedorOpcione::where('nombre', $opcion['name'])->first();

                        if ($Confirmarcontenedor) {
                            $contenedoresIds[] = $Confirmarcontenedor->id;
                        } else {
                            $contenedor = new ContenedorOpcione;
                            $contenedor->nombre = $opcion['name'];
                            $contenedor->tipo = $opcion['tipo'];
                            $contenedor->save();

                            // Almacenar los IDs de los contenedores creados
                            $contenedoresIds[] = $contenedor->id;

                            // Agregar las opciones para el contenedor
                            foreach ($opcion['opciones'] as $opcionContenedor) {
                                $opcionNueva = new Opcione;
                                $opcionNueva->nombre = $opcionContenedor['nombre'];
                                $opcionNueva->precio = $opcionContenedor['precio'];
                                $opcionNueva->contenedor_id = $contenedor->id;
                                $opcionNueva->save();
                            }
                        }
                    }
                    // Relacionar los contenedores con el producto utilizando el método sync()
                    $producto->contenedorOpciones()->sync($contenedoresIds);
                }

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
            $uploadedFileUrl = Cloudinary::upload($request->imagen->getRealPath(), ['folder' => 'desarrollo/productos', 'format' => 'avif']);
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

            $opcionesProducto = $request->opciones_producto;
            $contenedoresIds = [];
            if ($opcionesProducto) {
                foreach ($opcionesProducto as $opcion) {

                    $Confirmarcontenedor = ContenedorOpcione::where('nombre', $opcion['name'])->first();

                    if ($Confirmarcontenedor) {
                        $contenedoresIds[] = $Confirmarcontenedor->id;
                    } else {
                        $contenedor = new ContenedorOpcione;
                        $contenedor->nombre = $opcion['name'];
                        $contenedor->tipo = $opcion['tipo'];
                        $contenedor->save();

                        // Almacenar los IDs de los contenedores creados
                        $contenedoresIds[] = $contenedor->id;

                        // Agregar las opciones para el contenedor
                        foreach ($opcion['opciones'] as $opcionContenedor) {
                            $opcionNueva = new Opcione;
                            $opcionNueva->nombre = $opcionContenedor['nombre'];
                            $opcionNueva->precio = $opcionContenedor['precio'];
                            $opcionNueva->contenedor_id = $contenedor->id;
                            $opcionNueva->save();
                        }
                    }
                }
                // Relacionar los contenedores con el producto
                $productoNuevo->contenedorOpciones()->sync($contenedoresIds);
            }

            $productoCreado = Producto::with('promocion', 'contenedorOpciones.opciones')
            ->where('id', $productoNuevo->id) 
            ->first();

            return response()->json([
                'data' => $productoCreado,
                'success' => 'Producto agregado correctamente.'
            ]);
        }
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
            $uploadedFileUrl = Cloudinary::upload($request->imagen->getRealPath(), ['folder' => 'desarrollo/productos', 'format' => 'avif']);
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
            'productoId' => $producto->id,
            'categoriaAnterior' => $request->categoria_anterior,
            'categoriaActual' => $producto->categoria_id,
            'message' => 'producto' . ' ' . $producto->nombre . ' ' . 'movido a categoria' . ' ' . $request->nombre_categoria,
        ];
    }
}
