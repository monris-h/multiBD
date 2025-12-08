<?php

use Livewire\Volt\Component;
use App\Models\Comentario;
use App\Models\Log;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $entidad = '';
    public $entidad_id = '';
    public $usuario = '';
    public $contenido = '';
    public $calificacion = 5;
    public $editId = null;
    public $showModal = false;
    public $showDetailModal = false;
    public $search = '';
    public $filterEntidad = '';
    public $comentarioDetalle = null;

    public function render()
    {
        $comentarios = Comentario::query()
            ->when($this->search, function($query) {
                $query->where('contenido', 'like', '%' . $this->search . '%')
                    ->orWhere('usuario', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterEntidad, function($query) {
                $query->where('entidad', $this->filterEntidad);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $entidades = Comentario::distinct('entidad')->pluck('entidad');
        $promedioGeneral = round(Comentario::avg('calificacion') ?? 0, 2);

        return view('livewire.mongodb.comentarios', [
            'comentarios' => $comentarios,
            'entidades' => $entidades,
            'promedioGeneral' => $promedioGeneral,
        ]);
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->editId = $id;
        
        if ($id) {
            $comentario = Comentario::find($id);
            $this->entidad = $comentario->entidad;
            $this->entidad_id = $comentario->entidad_id;
            $this->usuario = $comentario->usuario;
            $this->contenido = $comentario->contenido;
            $this->calificacion = $comentario->calificacion;
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
            'entidad' => 'required|max:100',
            'entidad_id' => 'required',
            'usuario' => 'required|max:255',
            'contenido' => 'required|max:1000',
            'calificacion' => 'required|integer|min:1|max:5',
        ]);

        if ($this->editId) {
            $comentario = Comentario::find($this->editId);
            $comentario->update([
                'entidad' => $this->entidad,
                'entidad_id' => $this->entidad_id,
                'usuario' => $this->usuario,
                'contenido' => $this->contenido,
                'calificacion' => (int) $this->calificacion,
            ]);
            
            Log::registrar('actualizar', 'Comentario', $comentario->_id, "Comentario actualizado por {$this->usuario}");
            session()->flash('message', 'Comentario actualizado correctamente');
        } else {
            $comentario = Comentario::create([
                'entidad' => $this->entidad,
                'entidad_id' => $this->entidad_id,
                'usuario' => $this->usuario,
                'contenido' => $this->contenido,
                'calificacion' => (int) $this->calificacion,
            ]);
            
            Log::registrar('crear', 'Comentario', $comentario->_id, "Comentario creado por {$this->usuario}");
            session()->flash('message', 'Comentario creado correctamente');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $comentario = Comentario::find($id);
        if ($comentario) {
            Log::registrar('eliminar', 'Comentario', $id, "Comentario eliminado de {$comentario->usuario}");
            $comentario->delete();
            session()->flash('message', 'Comentario eliminado correctamente');
        }
    }

    public function showDetail($id)
    {
        $this->comentarioDetalle = Comentario::find($id);
        $this->showDetailModal = true;
    }

    private function resetForm()
    {
        $this->entidad = '';
        $this->entidad_id = '';
        $this->usuario = '';
        $this->contenido = '';
        $this->calificacion = 5;
        $this->editId = null;
    }
}; ?>

<div>
    <x-slot name="title">Comentarios - MongoDB</x-slot>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">💬 Comentarios</h1>
            <p class="text-gray-600 mt-1">🍃 MongoDB - Base de datos NoSQL</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-4">
            <div class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-lg">
                ⭐ Promedio: {{ $promedioGeneral }} / 5
            </div>
            <button wire:click="openModal" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">
                ➕ Nuevo Comentario
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
        <input type="text" wire:model.live="search" placeholder="🔍 Buscar por usuario o contenido..." 
            class="w-full md:w-1/3 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
        <select wire:model.live="filterEntidad" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            <option value="">Todas las entidades</option>
            @foreach($entidades as $ent)
                <option value="{{ $ent }}">{{ $ent }}</option>
            @endforeach
        </select>
    </div>

    <!-- Comments Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($comentarios as $comentario)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                <span class="text-green-600 font-bold">{{ strtoupper(substr($comentario->usuario, 0, 1)) }}</span>
                            </div>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">{{ $comentario->usuario }}</p>
                                <p class="text-xs text-gray-500">{{ $comentario->created_at->timezone('America/Mexico_City')->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="flex">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="{{ $i <= $comentario->calificacion ? 'text-yellow-400' : 'text-gray-300' }}">⭐</span>
                            @endfor
                        </div>
                    </div>
                    <p class="text-gray-700 mb-4">{{ Str::limit($comentario->contenido, 120) }}</p>
                    <div class="flex items-center justify-between text-sm">
                        <span class="bg-gray-100 px-2 py-1 rounded text-gray-600">
                            {{ $comentario->entidad }} #{{ $comentario->entidad_id }}
                        </span>
                        <div class="flex space-x-2">
                            <button wire:click="showDetail('{{ $comentario->_id }}')" class="text-gray-500 hover:text-gray-700">👁️</button>
                            <button wire:click="openModal('{{ $comentario->_id }}')" class="text-green-500 hover:text-green-700">✏️</button>
                            <button wire:click="delete('{{ $comentario->_id }}')" wire:confirm="¿Eliminar este comentario?" class="text-red-500 hover:text-red-700">🗑️</button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-white rounded-xl">
                <p class="text-gray-500">No hay comentarios registrados</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $comentarios->links() }}
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                <div class="px-6 py-4 border-b bg-green-50">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $editId ? '✏️ Editar Comentario' : '➕ Nuevo Comentario' }}
                    </h3>
                </div>
                <form wire:submit="save">
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Entidad *</label>
                                <input type="text" wire:model="entidad" placeholder="ej: Producto" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                                @error('entidad') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ID Entidad *</label>
                                <input type="text" wire:model="entidad_id" placeholder="ej: 1" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                                @error('entidad_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Usuario *</label>
                            <input type="text" wire:model="usuario" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                            @error('usuario') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Calificación *</label>
                            <div class="flex space-x-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="button" wire:click="$set('calificacion', {{ $i }})" 
                                        class="text-2xl {{ $i <= $calificacion ? 'text-yellow-400' : 'text-gray-300' }} hover:scale-110 transition">
                                        ⭐
                                    </button>
                                @endfor
                            </div>
                            @error('calificacion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Comentario *</label>
                            <textarea wire:model="contenido" rows="4" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"></textarea>
                            @error('contenido') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t bg-gray-50 flex justify-end space-x-3">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 border rounded-lg hover:bg-gray-100">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            {{ $editId ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Detail Modal -->
    @if($showDetailModal && $comentarioDetalle)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4">
                <div class="px-6 py-4 border-b flex justify-between items-center bg-green-50">
                    <h3 class="text-lg font-bold text-gray-900">💬 Detalle del Comentario</h3>
                    <button wire:click="$set('showDetailModal', false)" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                            <span class="text-green-600 font-bold text-lg">{{ strtoupper(substr($comentarioDetalle->usuario, 0, 1)) }}</span>
                        </div>
                        <div class="ml-4">
                            <p class="font-bold text-gray-900">{{ $comentarioDetalle->usuario }}</p>
                            <div class="flex">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="{{ $i <= $comentarioDetalle->calificacion ? 'text-yellow-400' : 'text-gray-300' }}">⭐</span>
                                @endfor
                                <span class="ml-2 text-sm text-gray-500">({{ $comentarioDetalle->calificacion }}/5)</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <p class="text-gray-700">{{ $comentarioDetalle->contenido }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Entidad</p>
                            <p class="font-medium">{{ $comentarioDetalle->entidad }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">ID Entidad</p>
                            <p class="font-medium">{{ $comentarioDetalle->entidad_id }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-gray-500">ID MongoDB</p>
                            <p class="font-mono text-xs bg-gray-100 p-2 rounded">{{ $comentarioDetalle->_id }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-gray-500">Fecha</p>
                            <p class="font-medium">{{ $comentarioDetalle->created_at->timezone('America/Mexico_City')->format('d/m/Y H:i:s') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
