<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'MultiBD - Sistema Multi-Base de Datos' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">
    <!-- Navegación -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                            </svg>
                        </div>
                        <span class="text-slate-800 font-semibold text-lg">MultiBD</span>
                    </a>
                </div>

                <!-- Menú Principal -->
                <div class="hidden md:flex items-center gap-1">
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        Dashboard
                    </a>
                    
                    <!-- MySQL Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="px-4 py-2 text-sm font-medium rounded-lg transition inline-flex items-center gap-1 {{ request()->routeIs('categorias.*', 'productos.*', 'clientes.*', 'ordenes.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            MySQL
                            <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak x-transition
                            class="absolute left-0 mt-2 w-48 rounded-xl shadow-lg bg-white ring-1 ring-slate-200 py-1">
                            <a href="{{ route('categorias.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Categorías</a>
                            <a href="{{ route('productos.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Productos</a>
                            <a href="{{ route('clientes.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Clientes</a>
                            <a href="{{ route('ordenes.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Órdenes</a>
                        </div>
                    </div>

                    <!-- MongoDB Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="px-4 py-2 text-sm font-medium rounded-lg transition inline-flex items-center gap-1 {{ request()->routeIs('logs.*', 'comentarios.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            MongoDB
                            <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak x-transition
                            class="absolute left-0 mt-2 w-48 rounded-xl shadow-lg bg-white ring-1 ring-slate-200 py-1">
                            <a href="{{ route('logs.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Logs</a>
                            <a href="{{ route('comentarios.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Comentarios</a>
                        </div>
                    </div>

                    <!-- Redis Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="px-4 py-2 text-sm font-medium rounded-lg transition inline-flex items-center gap-1 {{ request()->routeIs('configuraciones.*', 'sesiones.*') ? 'bg-red-50 text-red-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            Redis
                            <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak x-transition
                            class="absolute left-0 mt-2 w-48 rounded-xl shadow-lg bg-white ring-1 ring-slate-200 py-1">
                            <a href="{{ route('configuraciones.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Configuraciones</a>
                            <a href="{{ route('sesiones.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Sesiones</a>
                        </div>
                    </div>
                </div>

                <!-- Menú Móvil -->
                <div class="md:hidden" x-data="{ open: false }">
                    <button @click="open = !open" class="p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="absolute right-4 mt-2 w-56 bg-white rounded-xl shadow-lg ring-1 ring-slate-200 py-2">
                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Dashboard</a>
                        <div class="border-t border-slate-100 my-1"></div>
                        <p class="px-4 py-1 text-xs font-medium text-slate-400 uppercase">MySQL</p>
                        <a href="{{ route('categorias.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Categorías</a>
                        <a href="{{ route('productos.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Productos</a>
                        <a href="{{ route('clientes.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Clientes</a>
                        <a href="{{ route('ordenes.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Órdenes</a>
                        <div class="border-t border-slate-100 my-1"></div>
                        <p class="px-4 py-1 text-xs font-medium text-slate-400 uppercase">MongoDB</p>
                        <a href="{{ route('logs.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Logs</a>
                        <a href="{{ route('comentarios.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Comentarios</a>
                        <div class="border-t border-slate-100 my-1"></div>
                        <p class="px-4 py-1 text-xs font-medium text-slate-400 uppercase">Redis</p>
                        <a href="{{ route('configuraciones.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Configuraciones</a>
                        <a href="{{ route('sesiones.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Sesiones</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="flex-1 max-w-7xl w-full mx-auto py-8 px-4 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="flex items-center justify-center gap-6 text-sm text-slate-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    MySQL
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    MongoDB
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    Redis
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-2">
                {{ now()->timezone('America/Mexico_City')->format('d/m/Y H:i') }} · Ciudad de México
            </p>
        </div>
    </footer>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>
