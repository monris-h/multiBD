<?php

use Livewire\Volt\Component;
use App\Services\ConfiguracionService;
use App\Models\Log;

new class extends Component {
    public $clave = '';
    public $valor = '';
    public $descripcion = '';
    public $editKey = null;
    public $showModal = false;
    public $showDeletedModal = false;
    public $search = '';
    public $configuraciones = [];
    public $eliminadas = [];

    protected ConfiguracionService $service;

    public function boot(ConfiguracionService $service)
    {
        $this->service = $service;
    }

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $todas = $this->service->listar();
        $this->configuraciones = collect($todas)->filter(function($item) {
            if ($this->search) {
                return str_contains(strtolower($item['clave']), strtolower($this->search));
            }
            return true;
        })->values()->toArray();
    }

    public function render()
    {
        $this->loadData();
        return view('livewire.redis.configuraciones');
    }

    public function openModal($key = null)
    {
        $this->resetValidation();
        $this->editKey = $key;
        
        if ($key) {
            $config = $this->service->obtener($key);
            if ($config) {
                $this->clave = $key;
                $this->valor = $config['valor'] ?? '';
                $this->descripcion = $config['descripcion'] ?? '';
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
            'clave' => 'required|min:2|max:100',
            'valor' => 'required',
            'descripcion' => 'nullable|max:500',
        ]);

        if ($this->editKey) {
            $this->service->actualizar($this->editKey, [
                'valor' => $this->valor,
                'descripcion' => $this->descripcion,
            ]);
            
            Log::registrar('actualizar', 'Configuracion', $this->editKey, "Configuración actualizada: {$this->clave}");
            session()->flash('message', 'Configuración actualizada correctamente');
        } else {
            $this->service->crear($this->clave, [
                'valor' => $this->valor,
                'descripcion' => $this->descripcion,
            ]);
            
            Log::registrar('crear', 'Configuracion', $this->clave, "Configuración creada: {$this->clave}");
            session()->flash('message', 'Configuración creada correctamente');
        }

        $this->closeModal();
        $this->loadData();
    }

    public function delete($key)
    {
        $this->service->eliminar($key);
        Log::registrar('eliminar', 'Configuracion', $key, "Configuración eliminada: {$key}");
        session()->flash('message', 'Configuración eliminada correctamente');
        $this->loadData();
    }

    public function showDeleted()
    {
        $this->eliminadas = $this->service->listarEliminadas();
        $this->showDeletedModal = true;
    }

    public function restore($key)
    {
        $this->service->restaurar($key);
        Log::registrar('restaurar', 'Configuracion', $key, "Configuración restaurada: {$key}");
        session()->flash('message', 'Configuración restaurada correctamente');
        $this->eliminadas = $this->service->listarEliminadas();
        $this->loadData();
    }

    private function resetForm()
    {
        $this->clave = '';
        $this->valor = '';
        $this->descripcion = '';
        $this->editKey = null;
    }
}; ?>

<div>
    <x-slot name="title">Configuraciones - Redis</x-slot>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">⚙️ Configuraciones</h1>
            <p class="text-gray-600 mt-1">⚡ Redis - Base de datos Key-Value</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <button wire:click="showDeleted" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                🗑️ Ver Eliminadas
            </button>
            <button wire:click="openModal" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                ➕ Nueva Configuración
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
        <input type="text" wire:model.live="search" placeholder="🔍 Buscar configuración..." 
            class="w-full md:w-1/3 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
    </div>

    <!-- Stats -->
    <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-xl shadow-lg p-6 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-white/80">Total de Configuraciones</p>
                <p class="text-4xl font-bold">{{ count($configuraciones) }}</p>
            </div>
            <div class="text-6xl opacity-50">⚡</div>
        </div>
    </div>

    <!-- Configurations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($configuraciones as $config)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition">
                <div class="bg-red-50 px-4 py-3 border-b flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="text-red-600 text-xl mr-2">🔑</span>
                        <span class="font-mono font-bold text-gray-800">{{ $config['clave'] }}</span>
                    </div>
                    <div class="flex space-x-2">
                        <button wire:click="openModal('{{ $config['clave'] }}')" class="text-red-500 hover:text-red-700">✏️</button>
                        <button wire:click="delete('{{ $config['clave'] }}')" wire:confirm="¿Eliminar esta configuración?" class="text-gray-500 hover:text-gray-700">🗑️</button>
                    </div>
                </div>
                <div class="p-4">
                    <div class="mb-3">
                        <p class="text-xs text-gray-500 mb-1">Valor</p>
                        <p class="font-mono bg-gray-100 p-2 rounded text-sm break-all">{{ $config['valor'] ?? 'N/A' }}</p>
                    </div>
                    @if(isset($config['descripcion']) && $config['descripcion'])
                        <div class="mb-3">
                            <p class="text-xs text-gray-500 mb-1">Descripción</p>
                            <p class="text-sm text-gray-700">{{ $config['descripcion'] }}</p>
                        </div>
                    @endif
                    @if(isset($config['updated_at']))
                        <p class="text-xs text-gray-400">
                            Actualizado: {{ \Carbon\Carbon::parse($config['updated_at'])->timezone('America/Mexico_City')->format('d/m/Y H:i') }}
                        </p>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-white rounded-xl">
                <div class="text-6xl mb-4">⚙️</div>
                <p class="text-gray-500">No hay configuraciones registradas</p>
                <button wire:click="openModal" class="mt-4 text-red-600 hover:text-red-700 font-medium">
                    ➕ Crear primera configuración
                </button>
            </div>
        @endforelse
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                <div class="px-6 py-4 border-b bg-red-50">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $editKey ? '✏️ Editar Configuración' : '➕ Nueva Configuración' }}
                    </h3>
                </div>
                <form wire:submit="save">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Clave *</label>
                            <input type="text" wire:model="clave" {{ $editKey ? 'disabled' : '' }}
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 font-mono {{ $editKey ? 'bg-gray-100' : '' }}">
                            @error('clave') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor *</label>
                            <textarea wire:model="valor" rows="3" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 font-mono"></textarea>
                            @error('valor') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea wire:model="descripcion" rows="2" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-red-500"></textarea>
                            @error('descripcion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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

    <!-- Deleted Items Modal -->
    @if($showDeletedModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 max-h-[80vh] overflow-hidden">
                <div class="px-6 py-4 border-b flex justify-between items-center bg-red-50">
                    <h3 class="text-lg font-bold text-gray-900">🗑️ Configuraciones Eliminadas</h3>
                    <button wire:click="$set('showDeletedModal', false)" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div class="p-6 overflow-y-auto max-h-96">
                    @if(count($eliminadas) > 0)
                        <div class="space-y-3">
                            @foreach($eliminadas as $item)
                                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-mono font-medium">{{ $item['clave'] }}</p>
                                        <p class="text-sm text-gray-500">{{ $item['valor'] ?? 'N/A' }}</p>
                                    </div>
                                    <button wire:click="restore('{{ $item['clave'] }}')" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-sm">
                                        ♻️ Restaurar
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-gray-500">No hay configuraciones eliminadas</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
