<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public function index()
    {
        $caja = Caja::latest()->first();

        $haceDiezSemanas = now()->subWeeks(10);
        $datosCajas = Caja::where('created_at', '>=', $haceDiezSemanas)
        ->where('estado', 0)
        ->pluck('dinero');

        return [
            'caja' => $caja->dinero,
            'estado' => $caja->estado,
            'historia' => $datosCajas
        ];
    }
}
