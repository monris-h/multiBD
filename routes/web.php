<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\Mysql\Categorias;
use App\Livewire\Mysql\Productos;
use App\Livewire\Mysql\Clientes;
use App\Livewire\Mysql\Ordenes;
use App\Livewire\Mongodb\Logs;
use App\Livewire\Mongodb\Comentarios;
use App\Livewire\Redis\Configuraciones;
use App\Livewire\Redis\Sesiones;

// Dashboard Principal
Route::get('/', Dashboard::class)->name('dashboard');

// MySQL Routes
Route::get('/categorias', Categorias::class)->name('categorias.index');
Route::get('/productos', Productos::class)->name('productos.index');
Route::get('/clientes', Clientes::class)->name('clientes.index');
Route::get('/ordenes', Ordenes::class)->name('ordenes.index');

// MongoDB Routes
Route::get('/logs', Logs::class)->name('logs.index');
Route::get('/comentarios', Comentarios::class)->name('comentarios.index');

// Redis Routes
Route::get('/configuraciones', Configuraciones::class)->name('configuraciones.index');
Route::get('/sesiones', Sesiones::class)->name('sesiones.index');
