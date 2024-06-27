<?php

use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PromocioneController;
use App\Http\Controllers\ContenedorOpcionesController;

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
    Route::get('/user', [UserController::class, 'usuarioEnSession']);

    Route::post('/logout', [AuthController::class, 'logout']);

    //Almacenar Ordenes
    Route::get('/pedidos/pedidosCheques', [PedidoController::class, 'pedidosCheques']);
    Route::post('/pedidos/pedidosCheques/busqueda', [PedidoController::class, 'busquedaPedidos']);
    Route::get('/pedidos/{correo}', [PedidoController::class, 'index']);
    Route::post('/pedidos/nuevo', [PedidoController::class, 'store']);
    Route::put('/pedidos/actualizar/{id}', [PedidoController::class, 'update']);

    Route::apiResource('/usuarios', UserController::class);

    /*  Route::apiResource('/categorias', CategoriaController::class); */
    Route::post('/categorias/create', [CategoriaController::class, 'store']);
    Route::post('/categorias/update/{categoria}', [CategoriaController::class, 'update']);
    Route::get('/categorias/productos', [CategoriaController::class, 'categoriasProductos']);
    Route::delete('/categorias/eliminar/{categoria}', [CategoriaController::class, 'destroy']);

    Route::post('/productos/create', [ProductoController::class, 'store']);
    Route::put('/productos/disponible/{producto}', [ProductoController::class, 'updateDisponible']);
    Route::post('/productos/actualizar/{producto}', [ProductoController::class, 'productoActualizar']);
    Route::put('/productos/eliminar/{producto}', [ProductoController::class, 'productoEliminar']);
    Route::put('/productos/mover/{producto}', [ProductoController::class, 'cambiarCategoria']);

    Route::post('/promocion/create', [PromocioneController::class, 'store']);
    Route::get('/promociones', [PromocioneController::class, 'index']);

    /* Panel */
    Route::get('/datos/datosPanel', [PedidoController::class, 'datosPanel']);
    Route::get('/users/equipoTrabajo', [UserController::class, 'equipoTrabajo']);
    Route::get('/users', [UserController::class, 'index']);
    
    /* Registro */
    Route::get('/registros', [RegistroController::class, 'index']);
    
    /* caja */
    Route::get('/caja', [CajaController::class, 'index']);
    Route::post('/caja/abrir', [CajaController::class, 'store']);
    Route::post('/caja/cerrar', [CajaController::class, 'destroy']);
});

Route::put('/validate-token', function (Request $request) {
    if (Auth::guard('sanctum')->check()) {
        $user = $request->user();
        return response()->json(['user' => $user], 200);
    } else {
        return response()->json(['error' => 'Invalid token'], 401);
    }
})->middleware('auth:sanctum');

Route::get('/contenedores', [ContenedorOpcionesController::class, 'index']);

Route::get('/productos', [ProductoController::class, 'index']);
//Categorias
Route::get('/categorias', [CategoriaController::class, 'index']);
//Autenticacion
Route::post('/registro', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
