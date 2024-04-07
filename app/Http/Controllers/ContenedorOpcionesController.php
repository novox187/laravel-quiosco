<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContenedorOpcione;
use App\Http\Resources\ContenedorOpcioneResource;

class ContenedorOpcionesController extends Controller
{
    public function index()
    {
        $contenedores = ContenedorOpcione::with('opciones')->get();
        return response()->json(ContenedorOpcioneResource::collection($contenedores));
    }
}
