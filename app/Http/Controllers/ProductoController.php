<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Opcione;
use App\Models\Employee;
use App\Models\Producto;
use App\Models\Registro;
use Illuminate\Http\Request;
use App\Models\ContenedorOpcione;
use App\Http\Requests\ProductoRequest;
use App\Http\Resources\ProductoResource;
use App\Http\Resources\RegistroResource;
use App\Http\Requests\ProductoActualizarRequest;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\DB;

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
        $userId = $request->user()->id;
        $user = Employee::findOrFail($userId);
        $rol = $user->roles->first();

        if ($rol->rol !== 'admin') {
            return response()->json(['errors' => ['permisos' => ['No tienes el rol necesario para realizar esta acción']]], 422);
        }

        $datos = $request->validated();
        $producto = Producto::where('nombre', $request->nombre)->first();
        $registro = null;

        // Usar transacciones para asegurar la integridad de los datos
        DB::transaction(function () use ($request, &$producto, $datos, $userId, &$registro) {
            if ($producto) {
                $registro = $this->updateExistingProduct($producto, $request, $datos, $userId);
            } else {
                $producto = $this->createNewProduct($request, $datos, $userId);
                $registro = $this->logAction('crear', $userId, $producto);
            }
        });

        $productoNuevo = Producto::where('id', $producto->id)
            ->with('promocion', 'contenedorOpciones.opciones')
            ->whereHas('categoria', function ($query) {
                $query->where('eliminado', 0);
            })
            ->where('eliminado', 0)
            ->first();

        return response()->json([
            'success' => 'Producto agregado correctamente.',
            'producto' => new ProductoResource($productoNuevo), // Producto actualizado o recién creado '
            'registro' => $registro
        ]);
    }

    private function updateExistingProduct($producto, $request, $datos, $userId)
    {
        if ($producto->eliminado === 1) {
            $this->handleImageUpdate($producto, $request);
            $producto->eliminado = 0;
        } else {
            return response()->json(['errors' => ['campo1' => ['El producto ya existe.']]], 422);
        }

        $this->fillProductData($producto, $datos, $request);
        $producto->save();

        $this->handleOpcionesProducto($request->opciones_producto, $producto, $userId);

        return $this->logAction('actualizar', $userId, $producto);
    }

    private function createNewProduct($request, $datos, $userId)
    {
        $productoNuevo = new Producto;
        $this->fillProductData($productoNuevo, $datos, $request);
        $productoNuevo->save();

        $this->handleOpcionesProducto($request->opciones_producto, $productoNuevo, $userId);

        return $productoNuevo; // Retorna el producto recién creado
    }


    private function fillProductData($producto, $datos, $request)
    {
        $uploadedFileUrl = $request->imagen ? Cloudinary::upload($request->imagen->getRealPath(), ['folder' => 'productos', 'format' => 'avif']) : null;
        $producto->nombre = $datos['nombre'];
        $producto->precio = $datos['precio'];
        $producto->public_id = $uploadedFileUrl ? $uploadedFileUrl->getPublicId() : 'logo';
        $producto->imagen = $uploadedFileUrl ? $uploadedFileUrl->getSecurePath() : 'https://res.cloudinary.com/dfrsffngq/image/upload/v1723837093/logo.png';
        $producto->descripcion = $datos['descripcion'];
        $producto->categoria_id = $datos['categoria'];
        $producto->peso = $datos['peso'];
        $producto->tipo_peso = $datos['tipo_peso'];
        $producto->promo_id = null;
    }

    private function handleImageUpdate($producto, $request)
    {
        if ($producto->public_id !== 'logo') {
            Cloudinary::destroy($producto->public_id);
        }
    }

    private function logAction($accion, $userId, $producto)
    {
        // Crear un nuevo registro de la acción
        $registro = new Registro;
        $registro->accion = $accion; // Especificar la acción realizada (e.g., 'editar')
        $registro->employee_id = $userId; // Asociar el registro con el ID del empleado
        $registro->producto_id = $producto->id; // Asociar el registro con el ID del producto
        $registro->detalle = json_encode($producto); // Guardar los detalles del producto en formato JSON
        $registro->save(); // Guardar el registro en la base de datos

        // Obtener el registro recién creado con sus relaciones completas
        $registroConsulta = Registro::with('employee', 'pedido', 'categoria', 'producto', 'promocion')
            ->where('id', $registro->id)
            ->first();

        return $registroConsulta; // Retornar el registro creado
    }


    private function handleOpcionesProducto($opcionesProducto, $producto, $userId)
    {
        $contenedoresIds = [];

        if ($opcionesProducto) {
            foreach ($opcionesProducto as $opcion) {
                // Verificar si el contenedor ya existe
                $contenedor = ContenedorOpcione::firstOrCreate(
                    ['nombre' => $opcion['name']], // Condición para buscar
                    ['tipo' => $opcion['tipo']] // Datos a crear si no existe
                );

                // Agregar el ID del contenedor a la lista
                $contenedoresIds[] = $contenedor->id;

                // Registrar la acción solo si se creó un nuevo contenedor
                if ($contenedor->wasRecentlyCreated) {
                    $this->logAction('crear', $userId, $contenedor);
                }

                // Manejar las opciones dentro del contenedor
                foreach ($opcion['opciones'] as $opcionContenedor) {
                    // Verificar si la opción ya existe
                    $opcionExistente = Opcione::where('nombre', $opcionContenedor['nombre'])
                        ->where('contenedor_id', $contenedor->id)
                        ->first();

                    if (!$opcionExistente) {
                        // Crear nueva opción si no existe
                        Opcione::create([
                            'nombre' => $opcionContenedor['nombre'],
                            'precio' => $opcionContenedor['precio'],
                            'contenedor_id' => $contenedor->id,
                        ]);
                    }
                }
            }

            // Obtener los IDs de los contenedores existentes relacionados con el producto
            $contenedoresExistentes = $producto->contenedorOpciones()->pluck('contenedor_opciones.id')->toArray();

            // Combinar los IDs existentes con los nuevos
            $todosLosContenedoresIds = array_unique(array_merge($contenedoresExistentes, $contenedoresIds));

            // Relacionar los contenedores con el producto sin desvincular los existentes
            $producto->contenedorOpciones()->syncWithoutDetaching($todosLosContenedoresIds);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function productoActualizar(ProductoActualizarRequest $request, Producto $producto)
    {
        // Obtener el ID del usuario que realiza la petición
        $userId = $request->user()->id;
        // Buscar al empleado que realiza la petición
        $user = Employee::findOrFail($userId);
        // Obtener el primer rol del usuario
        $rol = $user->roles->first();

        // Verificar si el usuario tiene permisos para actualizar el producto
        if ($rol->rol !== 'admin' && $rol->editar !== 1) {
            return response()->json(['errors' => ['permisos' => ['No tienes el rol necesario para realizar esta acción']]], 422);
        }

        // Validar los datos del request
        $datos = $request->validated();
        $registro = null; // Inicializar como null para evitar problemas con la referencia

        // Usar transacciones para asegurar la integridad de los datos
        DB::transaction(function () use ($request, $producto, $datos, $userId, &$registro) {
            // Actualizar los datos del producto con la información validada
            $this->updateProductData($producto, $datos, $request);
            // Registrar la acción de edición del producto
            $registro = $this->logAction('editar', $userId, $producto);

            // Manejar las opciones del producto (ej. contenedores de opciones)
            $this->handleOpcionesProducto($request->opciones_producto, $producto, $userId);
        });

        // Traer los datos completos del producto actualizado junto con sus relaciones
        $productoActualizado = Producto::with('promocion', 'contenedorOpciones.opciones')
            ->where('id', $producto->id)
            ->first();

        // Retornar el producto actualizado y el registro de la acción
        return [
            'producto' => new ProductoResource($productoActualizado),
            'registro' => new RegistroResource($registro)
        ];
    }

    private function updateProductData($producto, $datos, $request)
    {
        if ($request->hasFile('imagen')) {
            // Eliminar la imagen anterior si no es la imagen por defecto
            if ($producto->public_id !== 'logo') {
                Cloudinary::destroy($producto->public_id);
            }

            // Subir la nueva imagen
            $uploadedFileUrl = Cloudinary::upload($request->imagen->getRealPath(), ['folder' => 'productos', 'format' => 'avif']);
            $producto->public_id = $uploadedFileUrl->getPublicId();
            $producto->imagen = $uploadedFileUrl->getSecurePath();
        } else {
            // Mantener la imagen anterior
            $producto->public_id = $producto->public_id; // No cambia
            $producto->imagen = $producto->imagen; // No cambia
        }

        // Actualizar otros datos del producto
        $producto->nombre = $datos['nombre'];
        $producto->precio = $datos['precio'];
        $producto->descripcion = $datos['descripcion'];
        $producto->peso = $datos['peso'];
        $producto->tipo_peso = $datos['tipo_peso'];
        $producto->promo_id = $request->promo_id;
        $producto->save();
    }

    public function desvincular(Request $request, $productoId)
    {
        $request->validate([
            'contenedores_ids' => 'required|array',
            'contenedores_ids.*' => 'exists:contenedor_opciones,id', // Asegúrate de que los IDs existan
        ]);

        return $this->desvincularContenedoresProducto($productoId, $request->contenedores_ids);
    }

    private function desvincularContenedoresProducto($productoId, $contenedoresIds)
    {
        // Obtener el producto por su ID
        $producto = Producto::find($productoId);

        // Verificar si el producto existe
        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado.'], 404);
        }

        // Desvincular los contenedores de opciones del producto
        $producto->contenedorOpciones()->detach($contenedoresIds);

        return response()->json(['success' => 'Contenedores desvinculados correctamente.']);
    }


    /* ACTUALIZACION DE ESTADO */
    public function updateDisponible(Producto $producto, Request $request)
    {
        $userId = $request->user()->id; //obtener el id del usuario del token de autenticacion
        $user = Employee::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        if ($rol->rol === 'admin') {

            if ($producto->disponible === 1) {
                $producto->disponible = 0;
                $producto->save();

                $registro = new Registro;
                $registro->accion = 'cambiar_estado';
                $registro->employee_id = $userId;
                $registro->producto_id = $producto->id;
                $registro->detalle = json_encode(
                    [
                        'disponible' => $producto->disponible,
                    ]
                );
                $registro->save();
            } else {
                $producto->disponible = 1;
                $producto->save();

                $registro = new Registro;
                $registro->accion = 'cambiar_estado';
                $registro->employee_id = $userId;
                $registro->producto_id = $producto->id;
                $registro->detalle = json_encode(
                    [
                        'disponible' => $producto->disponible,
                    ]
                );
                $registro->save();
            }

            $registros = Registro::where('id', $registro->id)
                ->with('employee', 'pedido', 'categoria', 'producto')
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
        $user = $user = Employee::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        if ($rol->rol === 'admin' || $rol->eliminar === 1) {

            if ($producto->public_id !== 'logo') {
                Cloudinary::destroy($producto->public_id);
            }

            $producto->eliminado = 1;
            $producto->save();

            $productoEliminado = Producto::where('id', $producto->id)->first();

            $registro = new Registro;
            $registro->accion = 'eliminar';
            $registro->employee_id = $userId;
            $registro->producto_id = $producto->id;
            $registro->detalle = json_encode($productoEliminado);
            $registro->save();

            $registros = Registro::where('id', $registro->id)
                ->with('employee', 'pedido', 'categoria', 'producto')
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
        $user = $user = Employee::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        if ($rol->rol === 'admin' || $rol->eliminar === 1) {
            $producto->categoria_id = $request->id_categoria;
            $producto->save();

            $registro = new Registro;
            $registro->accion = 'cambiar_categoria';
            $registro->employee_id = $userId;
            $registro->producto_id = $producto->id;
            $registro->detalle = json_encode(['categoria_id' => $request->id_categoria]);
            $registro->save();

            $registros = Registro::where('id', $registro->id)
                ->with('employee', 'pedido', 'categoria', 'producto')
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
