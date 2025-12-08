<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'MultiBD - Sistema Multi-Base de Datos' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Tailwind CDN como respaldo -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navegación -->
    <nav class="bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                        </svg>
                        <span class="ml-2 text-white font-bold text-xl">MultiBD</span>
                    </a>
                </div>

                <!-- Menú Principal -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="{{ route('dashboard') }}" class="text-white hover:bg-white/20 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-white/20' : '' }}">
                            📊 Dashboard
                        </a>
                        
                        <!-- MySQL Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="text-white hover:bg-white/20 px-3 py-2 rounded-md text-sm font-medium transition inline-flex items-center {{ request()->routeIs('categorias.*', 'productos.*', 'clientes.*', 'ordenes.*') ? 'bg-white/20' : '' }}">
                                🐬 MySQL
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak
                                class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                <div class="py-1">
                                    <a href="{{ route('categorias.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📁 Categorías</a>
                                    <a href="{{ route('productos.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📦 Productos</a>
                                    <a href="{{ route('clientes.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">👥 Clientes</a>
                                    <a href="{{ route('ordenes.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🛒 Órdenes</a>
                                </div>
                            </div>
                        </div>

                        <!-- MongoDB Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="text-white hover:bg-white/20 px-3 py-2 rounded-md text-sm font-medium transition inline-flex items-center {{ request()->routeIs('logs.*', 'comentarios.*') ? 'bg-white/20' : '' }}">
                                🍃 MongoDB
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak
                                class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                <div class="py-1">
                                    <a href="{{ route('logs.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📋 Logs</a>
                                    <a href="{{ route('comentarios.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">💬 Comentarios</a>
                                </div>
                            </div>
                        </div>

                        <!-- Redis Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="text-white hover:bg-white/20 px-3 py-2 rounded-md text-sm font-medium transition inline-flex items-center {{ request()->routeIs('configuraciones.*', 'sesiones.*') ? 'bg-white/20' : '' }}">
                                ⚡ Redis
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak
                                class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                <div class="py-1">
                                    <a href="{{ route('configuraciones.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">⚙️ Configuraciones</a>
                                    <a href="{{ route('sesiones.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🔐 Sesiones</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Menú Móvil -->
                <div class="md:hidden" x-data="{ open: false }">
                    <button @click="open = !open" class="text-white p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📊 Dashboard</a>
                        <hr>
                        <a href="{{ route('categorias.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📁 Categorías</a>
                        <a href="{{ route('productos.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📦 Productos</a>
                        <a href="{{ route('clientes.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">👥 Clientes</a>
                        <a href="{{ route('ordenes.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">🛒 Órdenes</a>
                        <hr>
                        <a href="{{ route('logs.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📋 Logs</a>
                        <a href="{{ route('comentarios.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">💬 Comentarios</a>
                        <hr>
                        <a href="{{ route('configuraciones.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">⚙️ Configuraciones</a>
                        <a href="{{ route('sesiones.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">🔐 Sesiones</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-sm">
                🐬 MySQL | 🍃 MongoDB | ⚡ Redis - Sistema Multi-Base de Datos
            </p>
            <p class="text-xs text-gray-400 mt-1">
                {{ now()->timezone('America/Mexico_City')->format('d/m/Y H:i:s') }} - México
            </p>
        </div>
    </footer>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
