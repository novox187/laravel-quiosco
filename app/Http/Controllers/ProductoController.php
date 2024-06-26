<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Opcione;
use App\Models\Producto;
use App\Models\Registro;
use Illuminate\Http\Request;
use App\Models\ContenedorOpcione;
use App\Http\Requests\ProductoRequest;
use App\Http\Resources\ProductoResource;
use App\Http\Resources\RegistroResource;
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
    public function store(ProductoRequest $request)
    {
        $userId = $request->user()->id; //obtener el id del usuario del token de autenticacion
        $user = User::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        if ($rol->rol === 'admin') { //valida que tenga los permisoso necesarios

            $datos = $request->validated();

            //obtenemos el producto eliminado
            $producto = Producto::where('nombre', $request->nombre)->first();

            // Validamos si el producto ya existe o esta eliminado
            if ($producto) {
                if ($producto->eliminado === 1) {

                    //Eliminamos la imagen anterior de la base de datos
                    if ($producto->public_id !== 'sldngq1rzkctsqpyz3tx') {
                        Cloudinary::destroy($producto->public_id);
                    }

                    //Subimos la nueva imagen
                    if ($request->imagen) {
                        $uploadedFileUrl = Cloudinary::upload($request->imagen->getRealPath(), ['folder' => 'productos', 'format' => 'avif']);
                        $url = $uploadedFileUrl->getSecurePath();
                        $public_id = $uploadedFileUrl->getPublicId();
                    } else {
                        $url = 'https://res.cloudinary.com/dfrsffngq/image/upload/v1718787004/sldngq1rzkctsqpyz3tx.png';
                        $public_id = 'sldngq1rzkctsqpyz3tx';
                    }

                    $producto->eliminado = 0;
                    $producto->precio = $datos['precio'];
                    $producto->public_id = $public_id;
                    $producto->imagen = $url;
                    $producto->descripcion = $datos['descripcion'];
                    $producto->categoria_id = $datos['categoria'];
                    $producto->peso = $datos['peso'];
                    $producto->tipo_peso = $datos['tipo_peso'];
                    $producto->save();

                    $registro = new Registro;
                    $registro->accion = 'crear';
                    $registro->user_id = $userId;
                    $registro->producto_id = $producto->id;
                    $registro->detalle = json_encode($producto);
                    $registro->save();


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

                                $registroContenedor = new Registro;
                                $registroContenedor->accion = 'crear';
                                $registroContenedor->user_id = $userId;
                                $registroContenedor->contenedor_id = $contenedor->id;
                                $registroContenedor->detalle = json_encode($contenedor);
                                $registroContenedor->save();

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

                    $productoCreado = Producto::with('promocion', 'contenedorOpciones.opciones')
                        ->where('id', $producto->id)
                        ->first();

                    return response()->json([
                        'data' => $productoCreado,
                        'success' => 'Producto agregado correctamente.'
                    ]);
                } else {
                    $errors = [
                        'campo1' => ['El producto ya existe.'],
                    ];
                    return response()->json(['errors' => $errors], 422);
                }
            } else {
                $url = '';
                $public_id = '';

                if ($request->imagen) {
                    $uploadedFileUrl = Cloudinary::upload($request->imagen->getRealPath(), ['folder' => 'productos', 'format' => 'avif']);
                    $url = $uploadedFileUrl->getSecurePath();
                    $public_id = $uploadedFileUrl->getPublicId();
                } else {
                    $url = 'https://res.cloudinary.com/dfrsffngq/image/upload/v1718787004/sldngq1rzkctsqpyz3tx.png';
                    $public_id = 'sldngq1rzkctsqpyz3tx';
                }

                $productoNuevo = new Producto;
                $productoNuevo->nombre = $datos['nombre'];
                $productoNuevo->precio = $datos['precio'];
                $productoNuevo->public_id = $public_id;
                $productoNuevo->imagen = $url;
                $productoNuevo->descripcion = $datos['descripcion'];
                $productoNuevo->categoria_id = $datos['categoria'];
                $productoNuevo->peso = $datos['peso'];
                $productoNuevo->tipo_peso = $datos['tipo_peso'];
                $productoNuevo->save();

                $registro = new Registro;
                $registro->accion = 'crear';
                $registro->user_id = $userId;
                $registro->producto_id = $productoNuevo->id;
                $registro->detalle = json_encode($productoNuevo);
                $registro->save();

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

                            $registroContenedor = new Registro;
                            $registroContenedor->accion = 'crear';
                            $registroContenedor->user_id = $userId;
                            $registroContenedor->contenedor_id = $contenedor->id;
                            $registroContenedor->detalle = json_encode($contenedor);
                            $registroContenedor->save();

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

                $registros = Registro::where('id', $registro->id)
                    ->with('user', 'pedido', 'categoria', 'producto', 'promocion')
                    ->first();

                return response()->json([
                    'data' => $productoCreado,
                    'success' => 'Producto agregado correctamente.',
                    'registro' => new RegistroResource($registros)
                ]);
            }
        } else {
            $errors = [
                'permisos' => ['No tienes el rol necesario para realizar esta accion'],
            ];
            return response()->json(['errors' => $errors], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function productoActualizar(ProductoActualizarRequest $request, Producto $producto)
    {
        $userId = $request->user()->id; //obtener el id del usuario del token de autenticacion
        $user = User::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        if ($rol->rol === 'admin' || $rol->editar === 1) { //valida que tenga los permisoso necesarios

            $datos = $request->validated();
            $registro = new Registro;

            if (empty($request->imagen)) {
                $producto->nombre = $datos['nombre'];
                $producto->precio = $datos['precio'];
                $producto->descripcion = $datos['descripcion'];
                $producto->peso = $datos['peso'];
                $producto->tipo_peso = $datos['tipo_peso'];
                $producto->promo_id = $request->promo_id;
                $producto->save();

                $registro->detalle = json_encode([
                    [
                        'nombre' => $datos['nombre'],
                        'precio' => $datos['precio'],
                        'descripcion' => $datos['descripcion'],
                        'peso' => $datos['peso'],
                        'tipo_peso' => $datos['tipo_peso'],
                        'promo_id' => $request->promo_id,
                    ]
                ]);
            } else {
                //Eliminamos la imagen anterior de la base de datos
                if ($producto->public_id !== 'sldngq1rzkctsqpyz3tx') {
                    Cloudinary::destroy($producto->public_id);
                }

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
                $producto->tipo_peso = $datos['tipo_peso'];
                $producto->promo_id = $request->promo_id;
                $producto->save();

                $registro->detalle = json_encode([
                    [
                        'nombre' => $datos['nombre'],
                        'precio' => $datos['precio'],
                        'public_id' => $public_id,
                        'url' => $url,
                        'descripcion' => $datos['descripcion'],
                        'peso' => $datos['peso'],
                        'tipo_peso' => $datos['tipo_peso'],
                        'promo_id' => $request->promo_id,
                    ]
                ]);
            }

            //registramos la accion realizada

            $registro->accion = 'editar';
            $registro->user_id = $userId;
            $registro->producto_id = $producto->id;
            $registro->save();

            $registros = Registro::where('id', $registro->id)
                ->with('user', 'pedido', 'categoria', 'producto')
                ->first();


            //traemos los datos completos del producto creado
            $productoActualizado = Producto::with('promocion', 'contenedorOpciones.opciones')
                ->where('id', $producto->id)
                ->first();

            //devolvemos el producto formateado con los datos necesarios
            return [
                'producto' => new ProductoResource($productoActualizado),
                'registro' => new RegistroResource($registros)
            ];
        } else {
            $errors = [
                'permisos' => ['No tienes el rol necesario para realizar esta accion'],
            ];
            return response()->json(['errors' => $errors], 422);
        }
    }
    public function updateDisponible(Producto $producto, Request $request)
    {
        $userId = $request->user()->id; //obtener el id del usuario del token de autenticacion
        $user = User::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        if ($rol->rol === 'admin') {

            if ($producto->disponible === 1) {
                $producto->disponible = 0;
                $producto->save();

                $registro = new Registro;
                $registro->accion = 'cambiar_estado';
                $registro->user_id = $userId;
                $registro->producto_id = $producto->id;
                $registro->detalle = json_encode([
                    [
                        'disponible' => $producto->disponible,
                    ]
                ]);
                $registro->save();
            } else {
                $producto->disponible = 1;
                $producto->save();

                $registro = new Registro;
                $registro->accion = 'cambiar_estado';
                $registro->user_id = $userId;
                $registro->producto_id = $producto->id;
                $registro->detalle = json_encode([
                    [
                        'disponible' => $producto->disponible,
                    ]
                ]);
                $registro->save();
            }

            $registros = Registro::where('id', $registro->id)
                ->with('user', 'pedido', 'categoria', 'producto')
                ->first();

            return [
                'producto' => $producto->id,
                'estado' => $producto->disponible,
                'registro' => new RegistroResource($registros)
            ];
        } else {
            $errors = [
                'permisos' => ['No tienes el rol necesario para realizar esta accion'],
            ];
            return response()->json(['errors' => $errors], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function productoEliminar(Producto $producto, Request $request)
    {
        $userId = $request->user()->id; //obtener el id del usuario del token de autenticacion
        $user = User::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        if ($rol->rol === 'admin' || $rol->eliminar === 1) {

            if ($producto->public_id !== 'sldngq1rzkctsqpyz3tx') {
                Cloudinary::destroy($producto->public_id);
            }

            $producto->eliminado = 1;
            $producto->save();

            $productoEliminado = Producto::where('id', $producto->id)->first();

            $registro = new Registro;
            $registro->accion = 'eliminar';
            $registro->user_id = $userId;
            $registro->producto_id = $producto->id;
            $registro->detalle = json_encode($productoEliminado);
            $registro->save();

            $registros = Registro::where('id', $registro->id)
                ->with('user', 'pedido', 'categoria', 'producto')
                ->first();

            return [
                'productoId' => $producto->id,
                'categoriaId' => $producto->categoria_id,
                'message' => 'producto' . ' ' . $producto->nombre . ' ' . 'eliminado',
                'registro' => new RegistroResource($registros)
            ];
        } else {
            $errors = [
                'permisos' => ['No tienes el rol necesario para realizar esta accion'],
            ];
            return response()->json(['errors' => $errors], 422);
        }
    }

    public function cambiarCategoria(Request $request, Producto $producto)
    {
        $userId = $request->user()->id; //obtener el id del usuario del token de autenticacion
        $user = User::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        if ($rol->rol === 'admin' || $rol->eliminar === 1) {
            $producto->categoria_id = $request->id_categoria;
            $producto->save();

            $registro = new Registro;
            $registro->accion = 'cambiar_categoria';
            $registro->user_id = $userId;
            $registro->producto_id = $producto->id;
            $registro->detalle = json_encode(['categoria_id' => $request->id_categoria]);
            $registro->save();

            $registros = Registro::where('id', $registro->id)
                ->with('user', 'pedido', 'categoria', 'producto')
                ->first();


            return [
                'productoId' => $producto->id,
                'categoriaAnterior' => $request->categoria_anterior,
                'categoriaActual' => $producto->categoria_id,
                'message' => 'producto' . ' ' . $producto->nombre . ' ' . 'movido a categoria' . ' ' . $request->nombre_categoria,
                'registro' => new RegistroResource($registros)
            ];
        } else {
            $errors = [
                'permisos' => ['No tienes el rol necesario para realizar esta accion'],
            ];
            return response()->json(['errors' => $errors], 422);
        }
    }
}
