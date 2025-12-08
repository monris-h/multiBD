<?php

use Livewire\Volt\Component;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Log;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $nombre = '';
    public $descripcion = '';
    public $precio = '';
    public $stock = '';
    public $categoria_id = '';
    public $editId = null;
    public $showModal = false;
    public $showDeletedModal = false;
    public $search = '';
    public $deletedItems = [];
    public $categorias = [];

    public function mount()
    {
        $this->categorias = Categoria::where('activo', true)->get();
    }

    public function render()
    {
        $productos = Producto::with('categoria')
            ->where('activo', true)
            ->when($this->search, function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.mysql.productos', [
            'productos' => $productos
        ]);
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->editId = $id;
        $this->categorias = Categoria::where('activo', true)->get();
        
        if ($id) {
            $producto = Producto::find($id);
            $this->nombre = $producto->nombre;
            $this->descripcion = $producto->descripcion;
            $this->precio = $producto->precio;
            $this->stock = $producto->stock;
            $this->categoria_id = $producto->categoria_id;
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
            'nombre' => 'required|min:2|max:255',
            'descripcion' => 'nullable|max:1000',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'categoria_id' => 'required|exists:categorias,id',
        ]);

        if ($this->editId) {
            $producto = Producto::find($this->editId);
            $producto->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'precio' => $this->precio,
                'stock' => $this->stock,
                'categoria_id' => $this->categoria_id,
            ]);
            
            Log::registrar('actualizar', 'Producto', $producto->id, "Producto actualizado: {$this->nombre}");
            session()->flash('message', 'Producto actualizado correctamente');
        } else {
            $producto = Producto::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'precio' => $this->precio,
                'stock' => $this->stock,
                'categoria_id' => $this->categoria_id,
                'activo' => true,
            ]);
            
            Log::registrar('crear', 'Producto', $producto->id, "Producto creado: {$this->nombre}");
            session()->flash('message', 'Producto creado correctamente');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $producto = Producto::find($id);
        if ($producto) {
            $producto->activo = false;
            $producto->save();
            $producto->delete();
            
            Log::registrar('eliminar', 'Producto', $id, "Producto eliminado: {$producto->nombre}");
            session()->flash('message', 'Producto eliminado correctamente');
        }
    }

    public function showDeleted()
    {
        $this->deletedItems = Producto::onlyTrashed()->get();
        $this->showDeletedModal = true;
    }

    public function restore($id)
    {
        $producto = Producto::onlyTrashed()->find($id);
        if ($producto) {
            $producto->restore();
            $producto->activo = true;
            $producto->save();
            
            Log::registrar('restaurar', 'Producto', $id, "Producto restaurado: {$producto->nombre}");
            session()->flash('message', 'Producto restaurado correctamente');
            $this->showDeleted();
        }
    }

    private function resetForm()
    {
        $this->nombre = '';
        $this->descripcion = '';
        $this->precio = '';
        $this->stock = '';
        $this->categoria_id = '';
        $this->editId = null;
    }
}; ?>

<div>
    <x-slot name="title">Productos - MySQL</x-slot>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">📦 Productos</h1>
            <p class="text-gray-600 mt-1">🐬 MySQL - Base de datos relacional</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <button wire:click="showDeleted" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                🗑️ Ver Eliminados
            </button>
            <button wire:click="openModal" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
                ➕ Nuevo Producto
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
        <input type="text" wire:model.live="search" placeholder="🔍 Buscar producto..." 
            class="w-full md:w-1/3 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($productos as $producto)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $producto->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $producto->nombre }}</div>
                            <div class="text-sm text-gray-500">{{ Str::limit($producto->descripcion, 30) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $producto->categoria->nombre ?? 'Sin categoría' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                            ${{ number_format($producto->precio, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $producto->stock > 10 ? 'bg-green-100 text-green-800' : ($producto->stock > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $producto->stock }} unidades
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $producto->activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="openModal({{ $producto->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">✏️</button>
                            <button wire:click="delete({{ $producto->id }})" wire:confirm="¿Estás seguro de eliminar este producto?" class="text-red-600 hover:text-red-900">🗑️</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No hay productos registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $productos->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $editId ? '✏️ Editar Producto' : '➕ Nuevo Producto' }}
                    </h3>
                </div>
                <form wire:submit="save">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                            <input type="text" wire:model="nombre" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                            @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Categoría *</label>
                            <select wire:model="categoria_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="">Seleccionar categoría</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                            @error('categoria_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Precio *</label>
                                <input type="number" step="0.01" wire:model="precio" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                @error('precio') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stock *</label>
                                <input type="number" wire:model="stock" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                @error('stock') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea wire:model="descripcion" rows="3" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                            @error('descripcion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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

    <!-- Deleted Items Modal -->
    @if($showDeletedModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 max-h-[80vh] overflow-hidden">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">🗑️ Productos Eliminados</h3>
                    <button wire:click="$set('showDeletedModal', false)" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div class="p-6 overflow-y-auto max-h-96">
                    @if(count($deletedItems) > 0)
                        <div class="space-y-3">
                            @foreach($deletedItems as $item)
                                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium">{{ $item->nombre }}</p>
                                        <p class="text-sm text-gray-500">${{ number_format($item->precio, 2) }} - Stock: {{ $item->stock }}</p>
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
