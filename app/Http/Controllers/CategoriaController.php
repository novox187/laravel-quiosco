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
        return new CategoriaCollection(Categoria::all());
    }

    //Optiene las categorias con sus productos relacionados
    public function categoriasProductos(){
                
        return new CategoriaProductoCollection(Categoria::with(['productos' => function ($query) {
            $query->where('eliminado', 0);
        }])->get());
    }

    public function store(CategoriaRequest $request)
    {

        $datos = $request->validated();

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
            'success' => 'Categoria guardada correctamente',
        ]);
    }
}
