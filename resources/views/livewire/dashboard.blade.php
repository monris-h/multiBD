<?php

use Livewire\Volt\Component;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Orden;
use App\Models\Log;
use App\Models\Comentario;
use App\Services\ConfiguracionService;
use App\Services\SesionCacheService;

new class extends Component {
    public $mysqlStats = [];
    public $mongoStats = [];
    public $redisStats = [];
    public $recentLogs = [];

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        // MySQL Stats
        $this->mysqlStats = [
            'categorias' => [
                'total' => Categoria::count(),
                'activos' => Categoria::where('activo', true)->count(),
            ],
            'productos' => [
                'total' => Producto::count(),
                'activos' => Producto::where('activo', true)->count(),
            ],
            'clientes' => [
                'total' => Cliente::count(),
                'activos' => Cliente::where('activo', true)->count(),
            ],
            'ordenes' => [
                'total' => Orden::count(),
                'activos' => Orden::where('activo', true)->count(),
                'pendientes' => Orden::where('estado', 'pendiente')->count(),
                'completadas' => Orden::where('estado', 'completada')->count(),
            ],
        ];

        // MongoDB Stats
        $this->mongoStats = [
            'logs' => Log::count(),
            'comentarios' => Comentario::count(),
            'comentarios_promedio' => round(Comentario::avg('calificacion') ?? 0, 2),
        ];

        // Redis Stats
        $configService = new ConfiguracionService();
        $sesionService = new SesionCacheService();
        
        $this->redisStats = [
            'configuraciones' => count($configService->listar()),
            'sesiones' => count($sesionService->listarTodas()),
        ];

        // Últimos logs
        $this->recentLogs = Log::orderBy('created_at', 'desc')->take(5)->get();
    }
}; ?>

<div>
    <x-slot name="title">Dashboard - MultiBD</x-slot>
    
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">📊 Dashboard</h1>
        <p class="text-gray-600 mt-2">Vista general del sistema Multi-Base de Datos</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- MySQL Card -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">🐬 MySQL</h2>
                <span class="bg-white/20 px-3 py-1 rounded-full text-sm">Relacional</span>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center bg-white/10 rounded-lg p-3">
                    <span>📁 Categorías</span>
                    <span class="font-bold">{{ $mysqlStats['categorias']['total'] }} ({{ $mysqlStats['categorias']['activos'] }} activos)</span>
                </div>
                <div class="flex justify-between items-center bg-white/10 rounded-lg p-3">
                    <span>📦 Productos</span>
                    <span class="font-bold">{{ $mysqlStats['productos']['total'] }} ({{ $mysqlStats['productos']['activos'] }} activos)</span>
                </div>
                <div class="flex justify-between items-center bg-white/10 rounded-lg p-3">
                    <span>👥 Clientes</span>
                    <span class="font-bold">{{ $mysqlStats['clientes']['total'] }} ({{ $mysqlStats['clientes']['activos'] }} activos)</span>
                </div>
                <div class="flex justify-between items-center bg-white/10 rounded-lg p-3">
                    <span>🛒 Órdenes</span>
                    <span class="font-bold">{{ $mysqlStats['ordenes']['total'] }} ({{ $mysqlStats['ordenes']['pendientes'] }} pend.)</span>
                </div>
            </div>
        </div>

        <!-- MongoDB Card -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">🍃 MongoDB</h2>
                <span class="bg-white/20 px-3 py-1 rounded-full text-sm">NoSQL</span>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center bg-white/10 rounded-lg p-3">
                    <span>📋 Logs</span>
                    <span class="font-bold">{{ $mongoStats['logs'] }}</span>
                </div>
                <div class="flex justify-between items-center bg-white/10 rounded-lg p-3">
                    <span>💬 Comentarios</span>
                    <span class="font-bold">{{ $mongoStats['comentarios'] }}</span>
                </div>
                <div class="flex justify-between items-center bg-white/10 rounded-lg p-3">
                    <span>⭐ Calificación Promedio</span>
                    <span class="font-bold">{{ $mongoStats['comentarios_promedio'] }} / 5</span>
                </div>
            </div>
        </div>

        <!-- Redis Card -->
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">⚡ Redis</h2>
                <span class="bg-white/20 px-3 py-1 rounded-full text-sm">Key-Value</span>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center bg-white/10 rounded-lg p-3">
                    <span>⚙️ Configuraciones</span>
                    <span class="font-bold">{{ $redisStats['configuraciones'] }}</span>
                </div>
                <div class="flex justify-between items-center bg-white/10 rounded-lg p-3">
                    <span>🔐 Sesiones Activas</span>
                    <span class="font-bold">{{ $redisStats['sesiones'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b">
            <h2 class="text-xl font-bold text-gray-800">📜 Actividad Reciente</h2>
        </div>
        <div class="p-6">
            @if(count($recentLogs) > 0)
                <div class="space-y-4">
                    @foreach($recentLogs as $log)
                        <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                @switch($log->accion)
                                    @case('crear')
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-green-100 text-green-600">➕</span>
                                        @break
                                    @case('actualizar')
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-600">✏️</span>
                                        @break
                                    @case('eliminar')
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-red-100 text-red-600">🗑️</span>
                                        @break
                                    @case('restaurar')
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-yellow-100 text-yellow-600">♻️</span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 text-gray-600">📝</span>
                                @endswitch
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ ucfirst($log->accion) }} en {{ $log->entidad }}</p>
                                <p class="text-sm text-gray-500">{{ $log->descripcion }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $log->created_at->timezone('America/Mexico_City')->format('d/m/Y H:i:s') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>No hay actividad reciente</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('categorias.index') }}" class="bg-white p-4 rounded-lg shadow hover:shadow-md transition text-center">
            <span class="text-3xl">📁</span>
            <p class="mt-2 font-medium text-gray-700">Categorías</p>
        </a>
        <a href="{{ route('productos.index') }}" class="bg-white p-4 rounded-lg shadow hover:shadow-md transition text-center">
            <span class="text-3xl">📦</span>
            <p class="mt-2 font-medium text-gray-700">Productos</p>
        </a>
        <a href="{{ route('logs.index') }}" class="bg-white p-4 rounded-lg shadow hover:shadow-md transition text-center">
            <span class="text-3xl">📋</span>
            <p class="mt-2 font-medium text-gray-700">Ver Logs</p>
        </a>
        <a href="{{ route('configuraciones.index') }}" class="bg-white p-4 rounded-lg shadow hover:shadow-md transition text-center">
            <span class="text-3xl">⚙️</span>
            <p class="mt-2 font-medium text-gray-700">Config</p>
        </a>
    </div>
</div>
