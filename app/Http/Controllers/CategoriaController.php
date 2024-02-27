<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriaRequest;
use App\Http\Resources\CategoriaCollection;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index(){
        
        return new CategoriaCollection(Categoria::all());
    }
    public function store(CategoriaRequest $request){

        $datos = $request->validated();

        $iconoName = 'icono_'.time().'.'.$request->icono->extension();  
        $nombreLimpio = pathinfo($iconoName, PATHINFO_FILENAME);
   
        $request->icono->move(public_path('img'), $iconoName);

        $categorias = new Categoria;
        $categorias->nombre =$request->nombre;
        $categorias->icono = $nombreLimpio;
        $categorias->save();

        return response()->json(['success'=>'Categoria guardada correctamente.']);
    }
}
