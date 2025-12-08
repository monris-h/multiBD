<?php

use Livewire\Volt\Component;
use App\Models\Log;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $filterAccion = '';
    public $filterEntidad = '';
    public $showDetailModal = false;
    public $logDetalle = null;

    public function render()
    {
        $logs = Log::query()
            ->when($this->search, function($query) {
                $query->where('descripcion', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterAccion, function($query) {
                $query->where('accion', $this->filterAccion);
            })
            ->when($this->filterEntidad, function($query) {
                $query->where('entidad', $this->filterEntidad);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $acciones = Log::distinct('accion')->pluck('accion');
        $entidades = Log::distinct('entidad')->pluck('entidad');

        return view('livewire.mongodb.logs', [
            'logs' => $logs,
            'acciones' => $acciones,
            'entidades' => $entidades,
        ]);
    }

    public function showDetail($id)
    {
        $this->logDetalle = Log::find($id);
        $this->showDetailModal = true;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->filterAccion = '';
        $this->filterEntidad = '';
    }
}; ?>

<div>
    <x-slot name="title">Logs - MongoDB</x-slot>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">📋 Logs de Actividad</h1>
            <p class="text-gray-600 mt-1">🍃 MongoDB - Base de datos NoSQL</p>
        </div>
        <div class="mt-4 md:mt-0">
            <span class="bg-green-100 text-green-800 px-4 py-2 rounded-lg text-sm font-medium">
                📊 Total: {{ App\Models\Log::count() }} registros
            </span>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">🔍 Buscar</label>
                <input type="text" wire:model.live="search" placeholder="Buscar en descripción..." 
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">⚡ Acción</label>
                <select wire:model.live="filterAccion" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">Todas las acciones</option>
                    @foreach($acciones as $accion)
                        <option value="{{ $accion }}">{{ ucfirst($accion) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">📦 Entidad</label>
                <select wire:model.live="filterEntidad" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">Todas las entidades</option>
                    @foreach($entidades as $entidad)
                        <option value="{{ $entidad }}">{{ $entidad }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button wire:click="clearFilters" class="w-full px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition">
                    🔄 Limpiar filtros
                </button>
            </div>
        </div>
    </div>

    <!-- Logs List -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entidad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Entidad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->created_at->timezone('America/Mexico_City')->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $accionClases = [
                                        'crear' => 'bg-green-100 text-green-800',
                                        'actualizar' => 'bg-blue-100 text-blue-800',
                                        'eliminar' => 'bg-red-100 text-red-800',
                                        'restaurar' => 'bg-yellow-100 text-yellow-800',
                                        'consultar' => 'bg-purple-100 text-purple-800',
                                    ];
                                    $accionIconos = [
                                        'crear' => '➕',
                                        'actualizar' => '✏️',
                                        'eliminar' => '🗑️',
                                        'restaurar' => '♻️',
                                        'consultar' => '👁️',
                                    ];
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $accionClases[$log->accion] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $accionIconos[$log->accion] ?? '📝' }} {{ ucfirst($log->accion) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $log->entidad }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->entidad_id ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ Str::limit($log->descripcion, 40) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->ip ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="showDetail('{{ $log->_id }}')" class="text-green-600 hover:text-green-900">
                                    👁️ Ver
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No hay logs registrados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t">
            {{ $logs->links() }}
        </div>
    </div>

    <!-- Detail Modal -->
    @if($showDetailModal && $logDetalle)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4">
                <div class="px-6 py-4 border-b flex justify-between items-center bg-green-50">
                    <h3 class="text-lg font-bold text-gray-900">📋 Detalle del Log</h3>
                    <button wire:click="$set('showDetailModal', false)" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <p class="text-sm text-gray-500">ID MongoDB</p>
                            <p class="font-mono text-sm bg-gray-100 p-2 rounded">{{ $logDetalle->_id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Acción</p>
                            <p class="font-medium">{{ ucfirst($logDetalle->accion) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Entidad</p>
                            <p class="font-medium">{{ $logDetalle->entidad }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">ID Entidad</p>
                            <p class="font-medium">{{ $logDetalle->entidad_id ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">IP</p>
                            <p class="font-medium">{{ $logDetalle->ip ?? 'N/A' }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-500">Descripción</p>
                            <p class="font-medium">{{ $logDetalle->descripcion }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-500">Fecha y Hora</p>
                            <p class="font-medium">{{ $logDetalle->created_at->timezone('America/Mexico_City')->format('d/m/Y H:i:s') }}</p>
                        </div>
                        @if($logDetalle->datos_adicionales)
                            <div class="col-span-2">
                                <p class="text-sm text-gray-500">Datos Adicionales</p>
                                <pre class="bg-gray-100 p-3 rounded text-sm overflow-x-auto">{{ json_encode($logDetalle->datos_adicionales, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
