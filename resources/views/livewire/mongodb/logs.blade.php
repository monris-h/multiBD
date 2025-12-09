<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Logs del Sistema</h1>
            <p class="text-slate-500 mt-1">Registro de actividad en MongoDB</p>
        </div>
        <button wire:click="clearFilters" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">Limpiar filtros</button>
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6">{{ session('message') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input wire:model.live="search" type="text" placeholder="Buscar en logs..." class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
            </div>
            <select wire:model.live="filterAccion" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                <option value="">Todas las acciones</option>
                @foreach($acciones as $accion)
                    <option value="{{ $accion }}">{{ ucfirst($accion) }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterEntidad" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                <option value="">Todas las entidades</option>
                @foreach($entidades as $entidad)
                    <option value="{{ $entidad }}">{{ ucfirst($entidad) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Acción</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Entidad</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Descripción</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Usuario</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">IP</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Fecha</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Ver</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($logs as $log)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full 
                                @if($log->accion === 'crear') bg-emerald-50 text-emerald-700
                                @elseif($log->accion === 'actualizar') bg-amber-50 text-amber-700
                                @elseif($log->accion === 'eliminar') bg-red-50 text-red-700
                                @elseif($log->accion === 'restaurar') bg-blue-50 text-blue-700
                                @else bg-slate-100 text-slate-700 @endif">
                                {{ $log->accion }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $log->entidad }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ Str::limit($log->descripcion, 40) }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $log->usuario ?? 'Sistema' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500 font-mono">{{ $log->ip ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="showDetail('{{ $log->_id }}')" class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition">Detalle</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-slate-500">No hay logs registrados</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $logs->links() }}</div>

    @if($showDetailModal && $logDetalle)
    <div class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">Detalle del Log</h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-slate-500">ID:</span> <span class="font-mono text-xs text-slate-700">{{ $logDetalle->_id }}</span></div>
                    <div><span class="text-slate-500">Acción:</span> 
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-slate-100 text-slate-700">{{ $logDetalle->accion }}</span>
                    </div>
                    <div><span class="text-slate-500">Entidad:</span> <span class="font-medium text-slate-900">{{ $logDetalle->entidad }}</span></div>
                    <div><span class="text-slate-500">Entidad ID:</span> <span class="font-medium text-slate-900">{{ $logDetalle->entidad_id ?? 'N/A' }}</span></div>
                    <div><span class="text-slate-500">Usuario:</span> <span class="font-medium text-slate-900">{{ $logDetalle->usuario ?? 'Sistema' }}</span></div>
                    <div><span class="text-slate-500">IP:</span> <span class="font-mono text-slate-700">{{ $logDetalle->ip ?? 'N/A' }}</span></div>
                </div>
                <div>
                    <span class="text-sm text-slate-500">Descripción:</span>
                    <p class="mt-1 text-sm text-slate-900">{{ $logDetalle->descripcion }}</p>
                </div>
                <div>
                    <span class="text-sm text-slate-500">Fecha:</span>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $logDetalle->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
                @if($logDetalle->datos_anteriores)
                    <div>
                        <span class="text-sm font-medium text-slate-700">Datos Anteriores:</span>
                        <pre class="bg-slate-50 border border-slate-200 p-3 rounded-lg text-xs mt-2 overflow-x-auto">{{ json_encode($logDetalle->datos_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
                @if($logDetalle->datos_nuevos)
                    <div>
                        <span class="text-sm font-medium text-slate-700">Datos Nuevos:</span>
                        <pre class="bg-slate-50 border border-slate-200 p-3 rounded-lg text-xs mt-2 overflow-x-auto">{{ json_encode($logDetalle->datos_nuevos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
            </div>
            <div class="px-6 py-4 border-t border-slate-200">
                <button wire:click="closeDetailModal" class="w-full px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">Cerrar</button>
            </div>
        </div>
    </div>
    @endif
</div>
