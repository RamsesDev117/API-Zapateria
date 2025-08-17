<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\TiendaController;
use App\Http\Middleware\CorsMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([CorsMiddleware::class])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        //Rutas del Superadmin y Admin
        Route::get('admin/roles', [UsuarioController::class, 'getRoles']);
        Route::get('admin/sucursales', [UsuarioController::class, 'getSucursales']);
        Route::post('admin/register', [UsuarioController::class, 'store']);
        Route::get('admin/usuarios', [UsuarioController::class, 'index']);
        Route::put('admin/usuarios/{id}', [UsuarioController::class, 'update']);
        Route::patch('admin/usuarios/{id}/desactivar', [UsuarioController::class, 'deactivate']);
        Route::patch('admin/usuarios/{id}/activar', [UsuarioController::class, 'activate']);
        Route::get('admin/ventas-sucursal', [TiendaController::class, 'ventasPorSucursal']);

        //Rutas para el usuario de bodega
        Route::post('bodega/registerZapatos', [InventarioController::class, 'storeZapatos']);
        Route::get('bodega/indexInventario', [InventarioController::class, 'indexInventario']);
        Route::post('bodega/storeInventario', [InventarioController::class, 'storeInventario']);
        Route::get('bodega/inventario-sucursal', [InventarioController::class, 'listarPorSucursal']);
        Route::post('bodega/trasladar-calzado', [InventarioController::class, 'trasladarZapato']);
        Route::put('bodega/editar-inventario/{id}', [InventarioController::class, 'actualizarUnidades']);
        Route::get('bodega/zapatos/{id}', [InventarioController::class, 'showZapato']);
        Route::get('bodega/getEmpBodega', [InventarioController::class, 'getEmpBodega']);
        Route::get('bodega/puntos-empleado', [InventarioController::class, 'filtrarPuntos']);
        Route::get('/zapatos', [InventarioController::class, 'indexZapatos']);

        //Rutas para el usuario de tienda
        Route::get('tienda/getZapato', [TiendaController::class, 'getZapato']);
        Route::get('tienda/getEmpBodega', [TiendaController::class, 'getEmpBodega']);
        Route::post('tienda/registrarVentas', [TiendaController::class, 'registrarVentas']);
        Route::get('tienda/ventas-usuario-dia', [TiendaController::class, 'ventasUsuarioDia']);
        Route::post('tienda/corte-caja', [TiendaController::class, 'corteCaja']);
        Route::get('tienda/obtener-corte-caja', [TiendaController::class, 'obtenerCorteCaja']);
        Route::post('tienda/registrar-apartado', [TiendaController::class, 'registrarApartado']);
        Route::get('tienda/listar-apartados', [TiendaController::class, 'listarApartados']);
        Route::post('tienda/apartado/{id}/completar', [TiendaController::class, 'completarApartado']);
        Route::post('tienda/apartado/{id}/cancelar', [TiendaController::class, 'cancelarApartado']);

        //Ruta para todos los usuarios autenticados
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});


