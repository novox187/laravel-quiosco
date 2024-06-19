<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Opcione;
use App\Models\Producto;
use App\Models\Registro;
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
        $userId = $request->user()->id; //obtener el id del usuario del token de autenticacion
        $user = User::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        $datos = $request->validated();

        $categoriaDB = Categoria::where('nombre', $datos['nombre'])->first();

        if ($rol->rol === 'admin') {
            if ($categoriaDB) {
                $uploadedFileUrl = Cloudinary::upload($request->icono->getRealPath(), ['folder' => 'categorias']);
                $url = $uploadedFileUrl->getSecurePath();
                $public_id = $uploadedFileUrl->getPublicId();

                $categoriaDB->icono = $url;
                $categoriaDB->public_id = $public_id;
                $categoriaDB->eliminado = 0;
                $categoriaDB->save();

                $registro = new Registro;
                $registro->accion = 'crear';
                $registro->user_id = $userId;
                $registro->categoria_id = $categoriaDB->id;
                $registro->detalle = json_encode($categoriaDB);
                $registro->save();

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

                $registro = new Registro;
                $registro->accion = 'crear';
                $registro->user_id = $userId;
                $registro->categoria_id = $categorias->id;
                $registro->detalle = json_encode($categorias);
                $registro->save();

                return response()->json([
                    'data' => $categorias,
                    'success' => 'Categoria creada correctamente',
                ]);
            }
        } else {
            $errors = [
                'permisos' => ['No tienes el rol necesario para realizar esta accion'],
            ];
            return response()->json(['errors' => $errors], 422);
        }
    }

    public function update(Request $request, Categoria $categoria)
    {
        $userId = $request->user()->id; //obtener el id del usuario del token de autenticacion
        $user = User::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        if ($rol->rol === 'admin') {

            if (empty($request->icono)) {
                $categoria->nombre = $request->nombre;
                $categoria->save();

                $registro = new Registro;
                $registro->accion = 'editar';
                $registro->user_id = $userId;
                $registro->categoria_id = $categoria->id;
                $registro->detalle = json_encode($categoria);
                $registro->save();
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

                $registro = new Registro;
                $registro->accion = 'editar';
                $registro->user_id = $userId;
                $registro->categoria_id = $categoria->id;
                $registro->detalle = json_encode($categoria);
                $registro->save();
            }
            $categoriaEditada = Categoria::where('id', $categoria->id)
                ->first();

            return [
                'id' => $categoriaEditada->id,
                'nombre' => $categoriaEditada->nombre,
                'icono' => $categoriaEditada->icono,
                'menssage' => 'categoria Actualizada',
            ];
        } else {
            $errors = [
                'permisos' => ['No tienes el rol necesario para realizar esta accion'],
            ];
            return response()->json(['errors' => $errors], 422);
        }
    }
    public function destroy(Categoria $categoria, Request $request)
    {
        $userId = $request->user()->id; //obtener el id del usuario del token de autenticacion
        $user = User::find($userId); // Obtener el usuario
        $rol = $user->roles->first(); // Obtener los roles del usuario

        if ($rol->rol === 'admin') {
            $categoria->eliminado = 1;
            $categoria->save();

            Cloudinary::destroy($categoria->public_id);

            $registro = new Registro;
            $registro->accion = 'eliminar';
            $registro->user_id = $userId;
            $registro->categoria_id = $categoria->id;
            $registro->detalle = json_encode($categoria);
            $registro->save();

            return [
                'id' => $categoria->id,
                'menssage' => 'categoria' . ' ' . $categoria->nombre . ' ' . 'eliminada',
            ];
        } else {
            $errors = [
                'permisos' => ['No tienes el rol necesario para realizar esta accion'],
            ];
            return response()->json(['errors' => $errors], 422);
        }
    }
}
