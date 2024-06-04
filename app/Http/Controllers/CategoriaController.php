<?php

namespace App\Http\Controllers;

use App\Models\Opcione;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Models\ContenedorOpcione;
use App\Http\Requests\ProductoRequest;
use App\Http\Requests\CategoriaRequest;
use App\Http\Resources\CategoriaCollection;
use App\Http\Resources\CategoriaProductoCollection;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::where('eliminado', 0)->get();
        return new CategoriaCollection($categorias);
    }

    //Optiene las categorias con sus productos relacionados
    public function categoriasProductos()
    {

        return new CategoriaProductoCollection(Categoria::with(['productos' => function ($query) {
            $query->where('eliminado', 0);
        }])
            ->where('eliminado', 0)
            ->get());
    }

    public function store(CategoriaRequest $request)
    {

        $datos = $request->validated();
        $categoriaDB = Categoria::where('nombre', $datos['nombre'])->first();
        if ($categoriaDB) {
            $uploadedFileUrl = Cloudinary::upload($request->icono->getRealPath(), ['folder' => 'categorias']);
            $url = $uploadedFileUrl->getSecurePath();
            $public_id = $uploadedFileUrl->getPublicId();

            $categoriaDB->icono = $url;
            $categoriaDB->public_id = $public_id;
            $categoriaDB->eliminado = 0;
            $categoriaDB->save();

            return response()->json([
                'data' => $categoriaDB,
                'success' => 'Categoria creada correctamente',
            ]);
        } else {

            $uploadedFileUrl = Cloudinary::upload($request->icono->getRealPath(), ['folder' => 'categorias']);
            $url = $uploadedFileUrl->getSecurePath();
            $public_id = $uploadedFileUrl->getPublicId();

            $categorias = new Categoria;
            $categorias->nombre = $datos['nombre'];
            $categorias->icono = $url;
            $categorias->public_id = $public_id;
            $categorias->save();


            return response()->json([
                'data' => $categorias,
                'success' => 'Categoria creada correctamente',
            ]);
        }
    }

    public function update(Request $request, Categoria $categoria)
    {
        if (empty($request->icono)) {
            $categoria->nombre = $request->nombre;
            $categoria->save();
        } else {
            //Eliminamos la imagen anterior de la base de datos
            Cloudinary::destroy($categoria->public_id);

            //Subimos la nueva imagen
            $uploadedFileUrl = Cloudinary::upload($request->icono->getRealPath(), ['folder' => 'categorias']);
            $url = $uploadedFileUrl->getSecurePath();
            $public_id = $uploadedFileUrl->getPublicId();

            $categoria->nombre = $request->nombre;
            $categoria->public_id = $public_id;
            $categoria->icono = $url;
            $categoria->save();
        }
        $categoriaEditada = Categoria::where('id', $categoria->id)
            ->first();

        return [
            'id' => $categoriaEditada->id,
            'nombre' => $categoriaEditada->nombre,
            'icono' => $categoriaEditada->icono,
            'menssage' => 'categoria Actualizada',
        ];
    }
    public function destroy(Categoria $categoria)
    {
        $categoria->eliminado = 1;
        $categoria->save();

        Cloudinary::destroy($categoria->public_id);

        return [
            'id' => $categoria->id,
            'menssage' => 'categoria' . ' ' . $categoria->nombre . ' ' . 'eliminada',
        ];
    }

    public function crearProducto(ProductoRequest $request)
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
                $uploadedFileUrl = Cloudinary::upload($request->imagen->getRealPath(), ['folder' => 'productos', 'format' => 'avif']);
                $url = $uploadedFileUrl->getSecurePath();
                $public_id = $uploadedFileUrl->getPublicId();

                $producto->eliminado = 0;
                $producto->precio = $datos['precio'];
                $producto->public_id = $public_id;
                $producto->imagen = $url;
                $producto->descripcion = $datos['descripcion'];
                $producto->categoria_id = $datos['categoria'];
                $producto->peso = $datos['peso'];
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
            $uploadedFileUrl = Cloudinary::upload($request->imagen->getRealPath(), ['folder' => 'productos', 'format' => 'avif']);
            $url = $uploadedFileUrl->getSecurePath();
            $public_id = $uploadedFileUrl->getPublicId();

            $productoNuevo = new Producto;
            $productoNuevo->nombre = $datos['nombre'];
            $productoNuevo->precio = $datos['precio'];
            $productoNuevo->public_id = $public_id;
            $productoNuevo->imagen = $url;
            $productoNuevo->descripcion = $datos['descripcion'];
            $productoNuevo->categoria_id = $datos['categoria'];
            $productoNuevo->peso = $datos['peso'];
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
}
