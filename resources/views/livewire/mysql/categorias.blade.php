<?php

use Livewire\Volt\Component;
use App\Models\Categoria;
use App\Models\Log;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $nombre = '';
    public $descripcion = '';
    public $editId = null;
    public $showModal = false;
    public $showDeletedModal = false;
    public $search = '';
    public $deletedItems = [];

    protected $rules = [
        'nombre' => 'required|min:2|max:255',
        'descripcion' => 'nullable|max:1000',
    ];

    public function render()
    {
        $categorias = Categoria::where('activo', true)
            ->when($this->search, function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.mysql.categorias', [
            'categorias' => $categorias
        ]);
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->editId = $id;
        
        if ($id) {
            $categoria = Categoria::find($id);
            $this->nombre = $categoria->nombre;
            $this->descripcion = $categoria->descripcion;
        } else {
            $this->nombre = '';
            $this->descripcion = '';
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
        $this->validate();

        if ($this->editId) {
            $categoria = Categoria::find($this->editId);
            $categoria->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
            ]);
            
            Log::registrar('actualizar', 'Categoria', $categoria->id, "Categoría actualizada: {$this->nombre}");
            session()->flash('message', 'Categoría actualizada correctamente');
        } else {
            $categoria = Categoria::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'activo' => true,
            ]);
            
            Log::registrar('crear', 'Categoria', $categoria->id, "Categoría creada: {$this->nombre}");
            session()->flash('message', 'Categoría creada correctamente');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $categoria = Categoria::find($id);
        if ($categoria) {
            $categoria->activo = false;
            $categoria->save();
            $categoria->delete();
            
            Log::registrar('eliminar', 'Categoria', $id, "Categoría eliminada: {$categoria->nombre}");
            session()->flash('message', 'Categoría eliminada correctamente');
        }
    }

    public function showDeleted()
    {
        $this->deletedItems = Categoria::onlyTrashed()->get();
        $this->showDeletedModal = true;
    }

    public function restore($id)
    {
        $categoria = Categoria::onlyTrashed()->find($id);
        if ($categoria) {
            $categoria->restore();
            $categoria->activo = true;
            $categoria->save();
            
            Log::registrar('restaurar', 'Categoria', $id, "Categoría restaurada: {$categoria->nombre}");
            session()->flash('message', 'Categoría restaurada correctamente');
            $this->showDeleted();
        }
    }

    private function resetForm()
    {
        $this->nombre = '';
        $this->descripcion = '';
        $this->editId = null;
    }
}; ?>

<div>
    <x-slot name="title">Categorías - MySQL</x-slot>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">📁 Categorías</h1>
            <p class="text-gray-600 mt-1">🐬 MySQL - Base de datos relacional</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <button wire:click="showDeleted" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                🗑️ Ver Eliminados
            </button>
            <button wire:click="openModal" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
                ➕ Nueva Categoría
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
        <input type="text" wire:model.live="search" placeholder="🔍 Buscar categoría..." 
            class="w-full md:w-1/3 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($categorias as $categoria)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $categoria->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $categoria->nombre }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($categoria->descripcion, 50) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $categoria->activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $categoria->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $categoria->created_at->timezone('America/Mexico_City')->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="openModal({{ $categoria->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">✏️ Editar</button>
                            <button wire:click="delete({{ $categoria->id }})" wire:confirm="¿Estás seguro de eliminar esta categoría?" class="text-red-600 hover:text-red-900">🗑️ Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No hay categorías registradas</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $categorias->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $editId ? '✏️ Editar Categoría' : '➕ Nueva Categoría' }}
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
                    <h3 class="text-lg font-bold text-gray-900">🗑️ Categorías Eliminadas</h3>
                    <button wire:click="$set('showDeletedModal', false)" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div class="p-6 overflow-y-auto max-h-96">
                    @if(count($deletedItems) > 0)
                        <div class="space-y-3">
                            @foreach($deletedItems as $item)
                                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium">{{ $item->nombre }}</p>
                                        <p class="text-sm text-gray-500">Eliminado: {{ $item->deleted_at->timezone('America/Mexico_City')->format('d/m/Y H:i') }}</p>
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
