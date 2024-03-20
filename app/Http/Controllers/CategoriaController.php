<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriaRequest;
use App\Http\Resources\CategoriaCollection;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {

        return new CategoriaCollection(Categoria::all());
    }
    public function store(CategoriaRequest $request)
    {

        $datos = $request->validated();
        //Agrega un nombre y con su extencion
        $iconoName = 'icono_' . $datos['nombre']. '.' . $request->icono->extension();

        $categorias = new Categoria;
        $categorias->nombre = $datos['nombre'];
        $categorias->icono = $iconoName;
        $categorias->save();
        //mueve la imagen a la carpeta public/img
        $request->icono->move(\public_path('img'), $iconoName);

        return response()->json(['success' => $categorias]);
    }
}
