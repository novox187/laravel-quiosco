<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PromocioneController;
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
    Route::get('/pedidos', [PedidoController::class, 'index']);
    Route::post('/pedidos/nuevo', [PedidoController::class, 'store']);
    Route::put('/pedidos/actualizar/{id}', [PedidoController::class, 'update']);

    Route::apiResource('/usuarios', UserController::class);

    /*  Route::apiResource('/categorias', CategoriaController::class); */
    Route::post('/categorias/create', [CategoriaController::class, 'store']);

    Route::post('/productos/create', [ProductoController::class, 'store']);
    Route::put('/productos/disponible/{producto}', [ProductoController::class, 'updateDisponible']);
    Route::put('/productos/actualizar/{producto}', [ProductoController::class, 'productoActualizar']);

    Route::post('/promocion/create', [PromocioneController::class, 'store']);
    Route::get('/promociones', [PromocioneController::class, 'index']);
});

Route::get('/productos/top', [PedidoController::class, 'productostop']);

Route::get('/productos', [ProductoController::class, 'index']);
//Categorias
Route::get('/categorias', [CategoriaController::class, 'index']);
Route::get('/categorias/productos', [CategoriaController::class, 'categoriasProductos']);
//Autenticacion
Route::post('/registro', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
