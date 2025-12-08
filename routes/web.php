<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Dashboard Principal
Volt::route('/', 'dashboard')->name('dashboard');

// MySQL Routes
Volt::route('/categorias', 'mysql.categorias')->name('categorias.index');
Volt::route('/productos', 'mysql.productos')->name('productos.index');
Volt::route('/clientes', 'mysql.clientes')->name('clientes.index');
Volt::route('/ordenes', 'mysql.ordenes')->name('ordenes.index');

// MongoDB Routes
Volt::route('/logs', 'mongodb.logs')->name('logs.index');
Volt::route('/comentarios', 'mongodb.comentarios')->name('comentarios.index');

// Redis Routes
Volt::route('/configuraciones', 'redis.configuraciones')->name('configuraciones.index');
Volt::route('/sesiones', 'redis.sesiones')->name('sesiones.index');
