<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    //Almacenar Ordenes
    Route::apiResource('/pedidos', PedidoController::class);


    Route::apiResource('/usuarios', UserController::class);

    Route::apiResource('/categorias', CategoriaController::class);
    /*     Route::apiResource('/productos', ProductoController::class); */
    Route::get('/productos', [ProductoController::class, 'index']);
    Route::put('/productos/disponible/{producto}', [ProductoController::class, 'updateDisponible']);
    Route::put('/productos/actualizar/{producto}', [ProductoController::class, 'productoActualizar']);
});


//Autenticacion
Route::post('/registro', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
