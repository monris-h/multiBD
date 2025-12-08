<div class="p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Dashboard - MultiBD</h1>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <!-- MySQL Stats -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-blue-600 mb-4">🗄️ MySQL (Relacional)</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <p class="text-gray-500 text-sm">Categorías</p>
                <p class="text-3xl font-bold text-gray-800">{{ $mysqlStats['categorias'] }}</p>
                <a href="/categorias" class="text-blue-500 text-sm hover:underline">Ver todas →</a>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <p class="text-gray-500 text-sm">Productos</p>
                <p class="text-3xl font-bold text-gray-800">{{ $mysqlStats['productos'] }}</p>
                <a href="/productos" class="text-blue-500 text-sm hover:underline">Ver todos →</a>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <p class="text-gray-500 text-sm">Clientes</p>
                <p class="text-3xl font-bold text-gray-800">{{ $mysqlStats['clientes'] }}</p>
                <a href="/clientes" class="text-blue-500 text-sm hover:underline">Ver todos →</a>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <p class="text-gray-500 text-sm">Órdenes</p>
                <p class="text-3xl font-bold text-gray-800">{{ $mysqlStats['ordenes'] }}</p>
                <a href="/ordenes" class="text-blue-500 text-sm hover:underline">Ver todas →</a>
            </div>
        </div>
    </div>

    <!-- MongoDB Stats -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-green-600 mb-4">📄 MongoDB (NoSQL)</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <p class="text-gray-500 text-sm">Logs</p>
                <p class="text-3xl font-bold text-gray-800">{{ $mongoStats['logs'] }}</p>
                <a href="/logs" class="text-green-500 text-sm hover:underline">Ver todos →</a>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <p class="text-gray-500 text-sm">Comentarios</p>
                <p class="text-3xl font-bold text-gray-800">{{ $mongoStats['comentarios'] }}</p>
                <a href="/comentarios" class="text-green-500 text-sm hover:underline">Ver todos →</a>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <p class="text-gray-500 text-sm">Calificación Promedio</p>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($mongoStats['calificacion_promedio'], 1) }} ⭐</p>
            </div>
        </div>
    </div>

    <!-- Redis Stats -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-red-600 mb-4">⚡ Redis (Key-Value)</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                <p class="text-gray-500 text-sm">Configuraciones</p>
                <p class="text-3xl font-bold text-gray-800">{{ $redisStats['configuraciones'] }}</p>
                <a href="/configuraciones" class="text-red-500 text-sm hover:underline">Ver todas →</a>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                <p class="text-gray-500 text-sm">Sesiones Activas</p>
                <p class="text-3xl font-bold text-gray-800">{{ $redisStats['sesiones'] }}</p>
                <a href="/sesiones" class="text-red-500 text-sm hover:underline">Ver todas →</a>
            </div>
        </div>
    </div>

    <!-- Logs Recientes -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Actividad Reciente</h2>
        @if(count($recentLogs) > 0)
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Entidad</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($recentLogs as $log)
                        <tr>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($log->accion === 'crear') bg-green-100 text-green-800
                                    @elseif($log->accion === 'actualizar') bg-yellow-100 text-yellow-800
                                    @elseif($log->accion === 'eliminar') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $log->accion }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-600">{{ $log->entidad }}</td>
                            <td class="px-4 py-2 text-sm text-gray-600">{{ $log->usuario ?? 'Sistema' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-500">{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-gray-500 text-center py-4">No hay actividad reciente</p>
        @endif
    </div>
</div>
