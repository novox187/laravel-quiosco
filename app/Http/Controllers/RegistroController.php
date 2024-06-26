<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Registro;
use Illuminate\Http\Request;
use App\Http\Resources\RegistroResource;

class RegistroController extends Controller
{
    public function index()
    {
        $registros = Registro::orderBy('id','desc')
        ->whereDate('created_at', Carbon::today())
        ->with('user', 'pedido', 'categoria', 'producto','promocion')
        ->limit(50)
        ->get();
        return RegistroResource::collection($registros);
    }
}
