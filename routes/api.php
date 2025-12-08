<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\OrdenController;
use App\Http\Controllers\Api\LogController;
use App\Http\Controllers\Api\ComentarioController;
use App\Http\Controllers\Api\ConfiguracionController;
use App\Http\Controllers\Api\SesionCacheController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rutas del Web Service Multi-Base de Datos
| - MySQL (Relacional): Categorías, Productos, Clientes, Órdenes
| - MongoDB (NoSQL): Logs, Comentarios
| - Redis (Clave-Valor): Configuraciones, Sesiones Cache
|
*/

// Ruta de información de la API
Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'API MultiBD - Web Service Multi-Base de Datos',
        'version' => '1.0.0',
        'bases_de_datos' => [
            'mysql' => [
                'tipo' => 'Relacional',
                'recursos' => ['categorias', 'productos', 'clientes', 'ordenes']
            ],
            'mongodb' => [
                'tipo' => 'NoSQL (Documentos)',
                'recursos' => ['logs', 'comentarios']
            ],
            'redis' => [
                'tipo' => 'Clave-Valor',
                'recursos' => ['configuraciones', 'sesiones']
            ]
        ],
        'endpoints' => [
            'categorias' => '/api/categorias',
            'productos' => '/api/productos',
            'clientes' => '/api/clientes',
            'ordenes' => '/api/ordenes',
            'logs' => '/api/logs',
            'comentarios' => '/api/comentarios',
            'configuraciones' => '/api/configuraciones',
            'sesiones' => '/api/sesiones'
        ]
    ]);
});

/*
|--------------------------------------------------------------------------
| MySQL Routes (Base de Datos Relacional)
|--------------------------------------------------------------------------
*/

// Categorías
Route::prefix('categorias')->group(function () {
    Route::get('/', [CategoriaController::class, 'index']);
    Route::post('/', [CategoriaController::class, 'store']);
    Route::get('/{id}', [CategoriaController::class, 'show']);
    Route::patch('/{id}', [CategoriaController::class, 'update']);
    Route::delete('/{id}', [CategoriaController::class, 'destroy']);
    Route::patch('/{id}/restaurar', [CategoriaController::class, 'restore']);
});

// Productos
Route::prefix('productos')->group(function () {
    Route::get('/', [ProductoController::class, 'index']);
    Route::post('/', [ProductoController::class, 'store']);
    Route::get('/{id}', [ProductoController::class, 'show']);
    Route::patch('/{id}', [ProductoController::class, 'update']);
    Route::delete('/{id}', [ProductoController::class, 'destroy']);
    Route::patch('/{id}/restaurar', [ProductoController::class, 'restore']);
});

// Clientes
Route::prefix('clientes')->group(function () {
    Route::get('/', [ClienteController::class, 'index']);
    Route::post('/', [ClienteController::class, 'store']);
    Route::get('/{id}', [ClienteController::class, 'show']);
    Route::patch('/{id}', [ClienteController::class, 'update']);
    Route::delete('/{id}', [ClienteController::class, 'destroy']);
    Route::patch('/{id}/restaurar', [ClienteController::class, 'restore']);
});

// Órdenes
Route::prefix('ordenes')->group(function () {
    Route::get('/estados', [OrdenController::class, 'estados']);
    Route::get('/', [OrdenController::class, 'index']);
    Route::post('/', [OrdenController::class, 'store']);
    Route::get('/{id}', [OrdenController::class, 'show']);
    Route::patch('/{id}', [OrdenController::class, 'update']);
    Route::delete('/{id}', [OrdenController::class, 'destroy']);
    Route::patch('/{id}/restaurar', [OrdenController::class, 'restore']);
});

/*
|--------------------------------------------------------------------------
| MongoDB Routes (Base de Datos NoSQL)
|--------------------------------------------------------------------------
*/

// Logs
Route::prefix('logs')->group(function () {
    Route::get('/acciones', [LogController::class, 'acciones']);
    Route::get('/', [LogController::class, 'index']);
    Route::post('/', [LogController::class, 'store']);
    Route::get('/{id}', [LogController::class, 'show']);
    Route::patch('/{id}', [LogController::class, 'update']);
    Route::delete('/{id}', [LogController::class, 'destroy']);
    Route::patch('/{id}/restaurar', [LogController::class, 'restore']);
});

// Comentarios
Route::prefix('comentarios')->group(function () {
    Route::get('/promedio/{entidad}/{entidad_id}', [ComentarioController::class, 'promedio']);
    Route::get('/', [ComentarioController::class, 'index']);
    Route::post('/', [ComentarioController::class, 'store']);
    Route::get('/{id}', [ComentarioController::class, 'show']);
    Route::patch('/{id}', [ComentarioController::class, 'update']);
    Route::delete('/{id}', [ComentarioController::class, 'destroy']);
    Route::patch('/{id}/restaurar', [ComentarioController::class, 'restore']);
});

/*
|--------------------------------------------------------------------------
| Redis Routes (Base de Datos Clave-Valor)
|--------------------------------------------------------------------------
*/

// Configuraciones
Route::prefix('configuraciones')->group(function () {
    Route::get('/eliminadas', [ConfiguracionController::class, 'deleted']);
    Route::get('/', [ConfiguracionController::class, 'index']);
    Route::post('/', [ConfiguracionController::class, 'store']);
    Route::get('/{clave}', [ConfiguracionController::class, 'show']);
    Route::patch('/{clave}', [ConfiguracionController::class, 'update']);
    Route::delete('/{clave}', [ConfiguracionController::class, 'destroy']);
    Route::patch('/{clave}/restaurar', [ConfiguracionController::class, 'restore']);
});

// Sesiones Cache
Route::prefix('sesiones')->group(function () {
    Route::get('/eliminadas', [SesionCacheController::class, 'deleted']);
    Route::get('/usuario/{usuario_id}', [SesionCacheController::class, 'byUsuario']);
    Route::get('/', [SesionCacheController::class, 'index']);
    Route::post('/', [SesionCacheController::class, 'store']);
    Route::get('/{id}', [SesionCacheController::class, 'show']);
    Route::patch('/{id}', [SesionCacheController::class, 'update']);
    Route::delete('/{id}', [SesionCacheController::class, 'destroy']);
    Route::patch('/{id}/restaurar', [SesionCacheController::class, 'restore']);
});
