<?php

use Livewire\Volt\Component;
use App\Services\SesionCacheService;
use App\Models\Log;

new class extends Component {
    public $usuario_id = '';
    public $datos = '';
    public $editKey = null;
    public $showModal = false;
    public $showDetailModal = false;
    public $search = '';
    public $sesiones = [];
    public $sesionDetalle = null;

    protected SesionCacheService $service;

    public function boot(SesionCacheService $service)
    {
        $this->service = $service;
    }

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $todas = $this->service->listarTodas();
        $this->sesiones = collect($todas)->filter(function($item) {
            if ($this->search) {
                return str_contains(strtolower($item['usuario_id'] ?? ''), strtolower($this->search));
            }
            return true;
        })->values()->toArray();
    }

    public function render()
    {
        $this->loadData();
        return view('livewire.redis.sesiones');
    }

    public function openModal($usuarioId = null)
    {
        $this->resetValidation();
        $this->editKey = $usuarioId;
        
        if ($usuarioId) {
            $sesion = $this->service->obtener($usuarioId);
            if ($sesion) {
                $this->usuario_id = $usuarioId;
                $this->datos = json_encode($sesion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
        } else {
            $this->resetForm();
        }
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save()
    {
        $this->validate([
            'usuario_id' => 'required',
            'datos' => 'required|json',
        ]);

        $datosArray = json_decode($this->datos, true);

        if ($this->editKey) {
            $this->service->actualizar($this->editKey, $datosArray);
            Log::registrar('actualizar', 'Sesion', $this->editKey, "Sesión actualizada para usuario: {$this->usuario_id}");
            session()->flash('message', 'Sesión actualizada correctamente');
        } else {
            $this->service->crear($this->usuario_id, $datosArray);
            Log::registrar('crear', 'Sesion', $this->usuario_id, "Sesión creada para usuario: {$this->usuario_id}");
            session()->flash('message', 'Sesión creada correctamente');
        }

        $this->closeModal();
        $this->loadData();
    }

    public function delete($usuarioId)
    {
        $this->service->eliminar($usuarioId);
        Log::registrar('eliminar', 'Sesion', $usuarioId, "Sesión eliminada para usuario: {$usuarioId}");
        session()->flash('message', 'Sesión eliminada correctamente');
        $this->loadData();
    }

    public function extendTTL($usuarioId)
    {
        $this->service->extenderTTL($usuarioId);
        Log::registrar('actualizar', 'Sesion', $usuarioId, "TTL extendido para sesión de usuario: {$usuarioId}");
        session()->flash('message', 'TTL de sesión extendido correctamente');
        $this->loadData();
    }

    public function showDetail($usuarioId)
    {
        $this->sesionDetalle = [
            'usuario_id' => $usuarioId,
            'datos' => $this->service->obtener($usuarioId),
            'ttl' => $this->service->obtenerTTL($usuarioId),
        ];
        $this->showDetailModal = true;
    }

    private function resetForm()
    {
        $this->usuario_id = '';
        $this->datos = '{}';
        $this->editKey = null;
    }
}; ?>

<div>
    <x-slot name="title">Sesiones - Redis</x-slot>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">🔐 Sesiones de Usuario</h1>
            <p class="text-gray-600 mt-1">⚡ Redis - Base de datos Key-Value (Cache)</p>
        </div>
        <div class="mt-4 md:mt-0">
            <button wire:click="openModal" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                ➕ Nueva Sesión
            </button>
        </div>
    </div>

    <!-- Flash Message -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <!-- Search -->
    <div class="mb-4">
        <input type="text" wire:model.live="search" placeholder="🔍 Buscar por ID de usuario..." 
            class="w-full md:w-1/3 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
    </div>

    <!-- Stats -->
    <div class="bg-gradient-to-r from-red-500 to-orange-500 rounded-xl shadow-lg p-6 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-white/80">Sesiones Activas</p>
                <p class="text-4xl font-bold">{{ count($sesiones) }}</p>
            </div>
            <div class="text-right">
                <p class="text-white/80">TTL por defecto</p>
                <p class="text-2xl font-bold">24 horas</p>
            </div>
            <div class="text-6xl opacity-50">🔐</div>
        </div>
    </div>

    <!-- Sessions Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-red-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datos</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TTL Restante</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($sesiones as $sesion)
                    @php
                        $ttl = $sesion['ttl'] ?? -1;
                        $ttlHoras = $ttl > 0 ? round($ttl / 3600, 1) : 0;
                        $isExpiring = $ttl > 0 && $ttl < 3600; // menos de 1 hora
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                                        <span class="text-red-600 font-bold">{{ strtoupper(substr($sesion['usuario_id'] ?? 'U', 0, 2)) }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $sesion['usuario_id'] ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-500 max-w-xs truncate">
                                @php
                                    $datosStr = json_encode($sesion['datos'] ?? []);
                                @endphp
                                {{ Str::limit($datosStr, 50) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($ttl > 0)
                                <span class="text-sm {{ $isExpiring ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                    {{ $ttlHoras }} horas
                                </span>
                            @elseif($ttl == -1)
                                <span class="text-sm text-green-600">Sin expiración</span>
                            @else
                                <span class="text-sm text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($isExpiring)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    ⚠️ Por expirar
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    ✅ Activa
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="showDetail('{{ $sesion['usuario_id'] }}')" class="text-gray-600 hover:text-gray-900 mr-2" title="Ver detalles">👁️</button>
                            <button wire:click="extendTTL('{{ $sesion['usuario_id'] }}')" class="text-blue-600 hover:text-blue-900 mr-2" title="Extender TTL">⏰</button>
                            <button wire:click="openModal('{{ $sesion['usuario_id'] }}')" class="text-red-600 hover:text-red-900 mr-2" title="Editar">✏️</button>
                            <button wire:click="delete('{{ $sesion['usuario_id'] }}')" wire:confirm="¿Eliminar esta sesión?" class="text-gray-500 hover:text-gray-700" title="Eliminar">🗑️</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <div class="text-6xl mb-4">🔐</div>
                            <p>No hay sesiones activas</p>
                            <button wire:click="openModal" class="mt-4 text-red-600 hover:text-red-700 font-medium">
                                ➕ Crear primera sesión
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4">
                <div class="px-6 py-4 border-b bg-red-50">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $editKey ? '✏️ Editar Sesión' : '➕ Nueva Sesión' }}
                    </h3>
                </div>
                <form wire:submit="save">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ID de Usuario *</label>
                            <input type="text" wire:model="usuario_id" {{ $editKey ? 'disabled' : '' }}
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 {{ $editKey ? 'bg-gray-100' : '' }}">
                            @error('usuario_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Datos (JSON) *</label>
                            <textarea wire:model="datos" rows="8" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 font-mono text-sm" placeholder='{"nombre": "Usuario", "email": "usuario@email.com"}'></textarea>
                            @error('datos') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            <p class="text-xs text-gray-500 mt-1">Ingresa los datos de sesión en formato JSON válido</p>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t bg-gray-50 flex justify-end space-x-3">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 border rounded-lg hover:bg-gray-100">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            {{ $editKey ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Detail Modal -->
    @if($showDetailModal && $sesionDetalle)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4">
                <div class="px-6 py-4 border-b flex justify-between items-center bg-red-50">
                    <h3 class="text-lg font-bold text-gray-900">🔐 Detalle de Sesión</h3>
                    <button wire:click="$set('showDetailModal', false)" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-500">Usuario ID</p>
                            <p class="font-bold text-lg">{{ $sesionDetalle['usuario_id'] }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">TTL Restante</p>
                            @php
                                $ttl = $sesionDetalle['ttl'] ?? -1;
                            @endphp
                            @if($ttl > 0)
                                <p class="font-bold text-lg">{{ round($ttl / 3600, 2) }} horas ({{ $ttl }} segundos)</p>
                            @elseif($ttl == -1)
                                <p class="font-bold text-lg text-green-600">Sin expiración</p>
                            @else
                                <p class="font-bold text-lg text-gray-400">Expirado</p>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-2">Datos de Sesión</p>
                        <pre class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto font-mono">{{ json_encode($sesionDetalle['datos'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                    <div class="mt-4 flex justify-end space-x-3">
                        <button wire:click="extendTTL('{{ $sesionDetalle['usuario_id'] }}')" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            ⏰ Extender TTL
                        </button>
                        <button wire:click="$set('showDetailModal', false)" class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
