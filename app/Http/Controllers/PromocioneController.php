<?php

namespace App\Http\Controllers;

use App\Http\Requests\PromocionRequest;
use App\Http\Resources\PromocionCollection;
use App\Models\Promocione;
use Illuminate\Http\Request;

class PromocioneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new PromocionCollection(Promocione::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PromocionRequest $request)
    {
        $datos = $request->validated();

        $promocion = new Promocione;
        $promocion->nombre = $datos['nombre_promo'];
        $promocion->descuento = $datos['porciento_promo'];
        $promocion->save();

        return [
            'nueva_promocion' => $promocion
        ];

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
