<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoriaProductoCollection;
use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Http\Requests\CategoriaRequest;
use App\Http\Resources\CategoriaCollection;
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
        ->where('eliminado',0)
        ->get());
    }

    public function store(CategoriaRequest $request)
    {
        $datos = $request->validated();

        $uploadedFileUrl = Cloudinary::upload($request->icono->getRealPath(), ['folder' => 'desarrollo/categorias']);
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

    public function update(Request $request, Categoria $categoria)
    {
        if (empty($request->icono)) {
            $categoria->nombre = $request->nombre;
            $categoria->save();
        } else {
            //Eliminamos la imagen anterior de la base de datos
            Cloudinary::destroy($categoria->public_id);

            //Subimos la nueva imagen
            $uploadedFileUrl = Cloudinary::upload($request->icono->getRealPath(), ['folder' => 'desarrollo/categorias']);
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
            'icono'=> $categoriaEditada->icono,
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
}
