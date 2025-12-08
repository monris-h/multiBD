<?php

use Livewire\Volt\Component;
use App\Models\Orden;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Log;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $cliente_id = '';
    public $estado = 'pendiente';
    public $total = 0;
    public $notas = '';
    public $productos_seleccionados = [];
    public $editId = null;
    public $showModal = false;
    public $showDeletedModal = false;
    public $showDetailModal = false;
    public $search = '';
    public $filterEstado = '';
    public $deletedItems = [];
    public $clientes = [];
    public $productos = [];
    public $ordenDetalle = null;

    public function mount()
    {
        $this->clientes = Cliente::where('activo', true)->get();
        $this->productos = Producto::where('activo', true)->get();
    }

    public function render()
    {
        $ordenes = Orden::with(['cliente', 'productos'])
            ->where('activo', true)
            ->when($this->search, function($query) {
                $query->whereHas('cliente', function($q) {
                    $q->where('nombre', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterEstado, function($query) {
                $query->where('estado', $this->filterEstado);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.mysql.ordenes', [
            'ordenes' => $ordenes
        ]);
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->editId = $id;
        $this->clientes = Cliente::where('activo', true)->get();
        $this->productos = Producto::where('activo', true)->get();
        
        if ($id) {
            $orden = Orden::with('productos')->find($id);
            $this->cliente_id = $orden->cliente_id;
            $this->estado = $orden->estado;
            $this->total = $orden->total;
            $this->notas = $orden->notas;
            $this->productos_seleccionados = $orden->productos->pluck('id')->toArray();
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

    public function calcularTotal()
    {
        $total = 0;
        foreach ($this->productos_seleccionados as $productoId) {
            $producto = Producto::find($productoId);
            if ($producto) {
                $total += $producto->precio;
            }
        }
        $this->total = $total;
    }

    public function save()
    {
        $this->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'estado' => 'required|in:pendiente,procesando,enviada,completada,cancelada',
            'productos_seleccionados' => 'required|array|min:1',
        ]);

        $this->calcularTotal();

        if ($this->editId) {
            $orden = Orden::find($this->editId);
            $orden->update([
                'cliente_id' => $this->cliente_id,
                'estado' => $this->estado,
                'total' => $this->total,
                'notas' => $this->notas,
            ]);
            $orden->productos()->sync($this->productos_seleccionados);
            
            Log::registrar('actualizar', 'Orden', $orden->id, "Orden actualizada: #{$orden->id}");
            session()->flash('message', 'Orden actualizada correctamente');
        } else {
            $orden = Orden::create([
                'cliente_id' => $this->cliente_id,
                'estado' => $this->estado,
                'total' => $this->total,
                'notas' => $this->notas,
                'activo' => true,
            ]);
            $orden->productos()->attach($this->productos_seleccionados);
            
            Log::registrar('crear', 'Orden', $orden->id, "Orden creada: #{$orden->id}");
            session()->flash('message', 'Orden creada correctamente');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $orden = Orden::find($id);
        if ($orden) {
            $orden->activo = false;
            $orden->save();
            $orden->delete();
            
            Log::registrar('eliminar', 'Orden', $id, "Orden eliminada: #{$id}");
            session()->flash('message', 'Orden eliminada correctamente');
        }
    }

    public function showDeleted()
    {
        $this->deletedItems = Orden::onlyTrashed()->with('cliente')->get();
        $this->showDeletedModal = true;
    }

    public function restore($id)
    {
        $orden = Orden::onlyTrashed()->find($id);
        if ($orden) {
            $orden->restore();
            $orden->activo = true;
            $orden->save();
            
            Log::registrar('restaurar', 'Orden', $id, "Orden restaurada: #{$id}");
            session()->flash('message', 'Orden restaurada correctamente');
            $this->showDeleted();
        }
    }

    public function showDetail($id)
    {
        $this->ordenDetalle = Orden::with(['cliente', 'productos'])->find($id);
        $this->showDetailModal = true;
    }

    public function updateEstado($id, $nuevoEstado)
    {
        $orden = Orden::find($id);
        if ($orden) {
            $orden->estado = $nuevoEstado;
            $orden->save();
            
            Log::registrar('actualizar', 'Orden', $id, "Estado de orden actualizado a: {$nuevoEstado}");
            session()->flash('message', 'Estado actualizado correctamente');
        }
    }

    private function resetForm()
    {
        $this->cliente_id = '';
        $this->estado = 'pendiente';
        $this->total = 0;
        $this->notas = '';
        $this->productos_seleccionados = [];
        $this->editId = null;
    }
}; ?>

<div>
    <x-slot name="title">Órdenes - MySQL</x-slot>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">🛒 Órdenes</h1>
            <p class="text-gray-600 mt-1">🐬 MySQL - Base de datos relacional</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <button wire:click="showDeleted" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                🗑️ Ver Eliminadas
            </button>
            <button wire:click="openModal" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
                ➕ Nueva Orden
            </button>
        </div>
    </div>

    <!-- Flash Message -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="mb-4 flex flex-col md:flex-row gap-4">
        <input type="text" wire:model.live="search" placeholder="🔍 Buscar por cliente..." 
            class="w-full md:w-1/3 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
        <select wire:model.live="filterEstado" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            <option value="">Todos los estados</option>
            <option value="pendiente">🕐 Pendiente</option>
            <option value="procesando">⚙️ Procesando</option>
            <option value="enviada">📦 Enviada</option>
            <option value="completada">✅ Completada</option>
            <option value="cancelada">❌ Cancelada</option>
        </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Productos</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($ordenes as $orden)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $orden->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $orden->cliente->nombre ?? 'Sin cliente' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $orden->productos->count() }} productos
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                            ${{ number_format($orden->total, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $estadoClases = [
                                    'pendiente' => 'bg-yellow-100 text-yellow-800',
                                    'procesando' => 'bg-blue-100 text-blue-800',
                                    'enviada' => 'bg-purple-100 text-purple-800',
                                    'completada' => 'bg-green-100 text-green-800',
                                    'cancelada' => 'bg-red-100 text-red-800',
                                ];
                                $estadoIconos = [
                                    'pendiente' => '🕐',
                                    'procesando' => '⚙️',
                                    'enviada' => '📦',
                                    'completada' => '✅',
                                    'cancelada' => '❌',
                                ];
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $estadoClases[$orden->estado] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $estadoIconos[$orden->estado] ?? '' }} {{ ucfirst($orden->estado) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $orden->created_at->timezone('America/Mexico_City')->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="showDetail({{ $orden->id }})" class="text-gray-600 hover:text-gray-900 mr-2">👁️</button>
                            <button wire:click="openModal({{ $orden->id }})" class="text-indigo-600 hover:text-indigo-900 mr-2">✏️</button>
                            <button wire:click="delete({{ $orden->id }})" wire:confirm="¿Estás seguro de eliminar esta orden?" class="text-red-600 hover:text-red-900">🗑️</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No hay órdenes registradas</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $ordenes->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $editId ? '✏️ Editar Orden' : '➕ Nueva Orden' }}
                    </h3>
                </div>
                <form wire:submit="save">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                            <select wire:model="cliente_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="">Seleccionar cliente</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->nombre }} ({{ $cliente->email }})</option>
                                @endforeach
                            </select>
                            @error('cliente_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select wire:model="estado" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="pendiente">🕐 Pendiente</option>
                                <option value="procesando">⚙️ Procesando</option>
                                <option value="enviada">📦 Enviada</option>
                                <option value="completada">✅ Completada</option>
                                <option value="cancelada">❌ Cancelada</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Productos *</label>
                            <div class="border rounded-lg p-3 max-h-48 overflow-y-auto space-y-2">
                                @foreach($productos as $producto)
                                    <label class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox" wire:model="productos_seleccionados" wire:change="calcularTotal" value="{{ $producto->id }}" class="rounded text-indigo-600">
                                        <span class="flex-1">{{ $producto->nombre }}</span>
                                        <span class="text-gray-500">${{ number_format($producto->precio, 2) }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('productos_seleccionados') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="font-medium">Total:</span>
                                <span class="text-2xl font-bold text-indigo-600">${{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                            <textarea wire:model="notas" rows="2" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t bg-gray-50 flex justify-end space-x-3">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 border rounded-lg hover:bg-gray-100">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            {{ $editId ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Detail Modal -->
    @if($showDetailModal && $ordenDetalle)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">👁️ Detalle de Orden #{{ $ordenDetalle->id }}</h3>
                    <button wire:click="$set('showDetailModal', false)" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-500">Cliente</p>
                            <p class="font-medium">{{ $ordenDetalle->cliente->nombre ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Estado</p>
                            <p class="font-medium">{{ ucfirst($ordenDetalle->estado) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Fecha</p>
                            <p class="font-medium">{{ $ordenDetalle->created_at->timezone('America/Mexico_City')->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total</p>
                            <p class="font-bold text-xl text-indigo-600">${{ number_format($ordenDetalle->total, 2) }}</p>
                        </div>
                    </div>
                    <div class="border-t pt-4">
                        <p class="text-sm text-gray-500 mb-2">Productos</p>
                        <div class="space-y-2">
                            @foreach($ordenDetalle->productos as $producto)
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span>{{ $producto->nombre }}</span>
                                    <span class="font-medium">${{ number_format($producto->precio, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @if($ordenDetalle->notas)
                        <div class="border-t pt-4 mt-4">
                            <p class="text-sm text-gray-500 mb-1">Notas</p>
                            <p class="text-gray-700">{{ $ordenDetalle->notas }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Deleted Items Modal -->
    @if($showDeletedModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 max-h-[80vh] overflow-hidden">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">🗑️ Órdenes Eliminadas</h3>
                    <button wire:click="$set('showDeletedModal', false)" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div class="p-6 overflow-y-auto max-h-96">
                    @if(count($deletedItems) > 0)
                        <div class="space-y-3">
                            @foreach($deletedItems as $item)
                                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium">Orden #{{ $item->id }}</p>
                                        <p class="text-sm text-gray-500">{{ $item->cliente->nombre ?? 'N/A' }} - ${{ number_format($item->total, 2) }}</p>
                                    </div>
                                    <button wire:click="restore({{ $item->id }})" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-sm">
                                        ♻️ Restaurar
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-gray-500">No hay elementos eliminados</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
