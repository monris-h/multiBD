<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">📋 Logs del Sistema (MongoDB)</h1>
        <button wire:click="clearFilters" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Limpiar Filtros</button>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('message') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <input wire:model.live="search" type="text" placeholder="Buscar en logs..." class="border rounded px-4 py-2">
        <select wire:model.live="filterAccion" class="border rounded px-4 py-2">
            <option value="">Todas las acciones</option>
            @foreach($acciones as $accion)
                <option value="{{ $accion }}">{{ ucfirst($accion) }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterEntidad" class="border rounded px-4 py-2">
            <option value="">Todas las entidades</option>
            @foreach($entidades as $entidad)
                <option value="{{ $entidad }}">{{ ucfirst($entidad) }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entidad</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ver</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($logs as $log)
                    <tr>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($log->accion === 'crear') bg-green-100 text-green-800
                                @elseif($log->accion === 'actualizar') bg-yellow-100 text-yellow-800
                                @elseif($log->accion === 'eliminar') bg-red-100 text-red-800
                                @elseif($log->accion === 'restaurar') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $log->accion }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $log->entidad }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ Str::limit($log->descripcion, 40) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $log->usuario ?? 'Sistema' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $log->ip ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-6 py-4">
                            <button wire:click="showDetail('{{ $log->_id }}')" class="text-blue-600 hover:text-blue-800">Detalle</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No hay logs registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>

    @if($showDetailModal && $logDetalle)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <h2 class="text-xl font-bold mb-4">Detalle del Log</h2>
            <div class="space-y-2">
                <p><strong>ID:</strong> {{ $logDetalle->_id }}</p>
                <p><strong>Acción:</strong> <span class="px-2 py-1 text-xs rounded-full bg-gray-100">{{ $logDetalle->accion }}</span></p>
                <p><strong>Entidad:</strong> {{ $logDetalle->entidad }}</p>
                <p><strong>Entidad ID:</strong> {{ $logDetalle->entidad_id ?? 'N/A' }}</p>
                <p><strong>Usuario:</strong> {{ $logDetalle->usuario ?? 'Sistema' }}</p>
                <p><strong>IP:</strong> {{ $logDetalle->ip ?? 'N/A' }}</p>
                <p><strong>Descripción:</strong> {{ $logDetalle->descripcion }}</p>
                <p><strong>Fecha:</strong> {{ $logDetalle->created_at->format('d/m/Y H:i:s') }}</p>
                @if($logDetalle->datos_anteriores)
                    <div>
                        <strong>Datos Anteriores:</strong>
                        <pre class="bg-gray-100 p-2 rounded text-xs mt-1 overflow-x-auto">{{ json_encode($logDetalle->datos_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
                @if($logDetalle->datos_nuevos)
                    <div>
                        <strong>Datos Nuevos:</strong>
                        <pre class="bg-gray-100 p-2 rounded text-xs mt-1 overflow-x-auto">{{ json_encode($logDetalle->datos_nuevos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
            </div>
            <div class="mt-4 flex justify-end">
                <button wire:click="closeDetailModal" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cerrar</button>
            </div>
        </div>
    </div>
    @endif
</div>
