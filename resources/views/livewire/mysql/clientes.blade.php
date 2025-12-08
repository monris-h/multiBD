<?php

use Livewire\Volt\Component;
use App\Models\Cliente;
use App\Models\Log;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $nombre = '';
    public $email = '';
    public $telefono = '';
    public $direccion = '';
    public $editId = null;
    public $showModal = false;
    public $showDeletedModal = false;
    public $search = '';
    public $deletedItems = [];

    public function render()
    {
        $clientes = Cliente::where('activo', true)
            ->when($this->search, function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.mysql.clientes', [
            'clientes' => $clientes
        ]);
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->editId = $id;
        
        if ($id) {
            $cliente = Cliente::find($id);
            $this->nombre = $cliente->nombre;
            $this->email = $cliente->email;
            $this->telefono = $cliente->telefono;
            $this->direccion = $cliente->direccion;
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
        $rules = [
            'nombre' => 'required|min:2|max:255',
            'email' => 'required|email|max:255',
            'telefono' => 'nullable|max:20',
            'direccion' => 'nullable|max:500',
        ];

        if (!$this->editId) {
            $rules['email'] .= '|unique:clientes,email';
        } else {
            $rules['email'] .= '|unique:clientes,email,' . $this->editId;
        }

        $this->validate($rules);

        if ($this->editId) {
            $cliente = Cliente::find($this->editId);
            $cliente->update([
                'nombre' => $this->nombre,
                'email' => $this->email,
                'telefono' => $this->telefono,
                'direccion' => $this->direccion,
            ]);
            
            Log::registrar('actualizar', 'Cliente', $cliente->id, "Cliente actualizado: {$this->nombre}");
            session()->flash('message', 'Cliente actualizado correctamente');
        } else {
            $cliente = Cliente::create([
                'nombre' => $this->nombre,
                'email' => $this->email,
                'telefono' => $this->telefono,
                'direccion' => $this->direccion,
                'activo' => true,
            ]);
            
            Log::registrar('crear', 'Cliente', $cliente->id, "Cliente creado: {$this->nombre}");
            session()->flash('message', 'Cliente creado correctamente');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $cliente = Cliente::find($id);
        if ($cliente) {
            $cliente->activo = false;
            $cliente->save();
            $cliente->delete();
            
            Log::registrar('eliminar', 'Cliente', $id, "Cliente eliminado: {$cliente->nombre}");
            session()->flash('message', 'Cliente eliminado correctamente');
        }
    }

    public function showDeleted()
    {
        $this->deletedItems = Cliente::onlyTrashed()->get();
        $this->showDeletedModal = true;
    }

    public function restore($id)
    {
        $cliente = Cliente::onlyTrashed()->find($id);
        if ($cliente) {
            $cliente->restore();
            $cliente->activo = true;
            $cliente->save();
            
            Log::registrar('restaurar', 'Cliente', $id, "Cliente restaurado: {$cliente->nombre}");
            session()->flash('message', 'Cliente restaurado correctamente');
            $this->showDeleted();
        }
    }

    private function resetForm()
    {
        $this->nombre = '';
        $this->email = '';
        $this->telefono = '';
        $this->direccion = '';
        $this->editId = null;
    }
}; ?>

<div>
    <x-slot name="title">Clientes - MySQL</x-slot>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">👥 Clientes</h1>
            <p class="text-gray-600 mt-1">🐬 MySQL - Base de datos relacional</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <button wire:click="showDeleted" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                🗑️ Ver Eliminados
            </button>
            <button wire:click="openModal" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
                ➕ Nuevo Cliente
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
        <input type="text" wire:model.live="search" placeholder="🔍 Buscar cliente por nombre o email..." 
            class="w-full md:w-1/3 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dirección</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($clientes as $cliente)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cliente->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-indigo-600 font-medium">{{ strtoupper(substr($cliente->nombre, 0, 2)) }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $cliente->nombre }}</div>
                                    <div class="text-sm text-gray-500">{{ $cliente->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $cliente->telefono ?? 'No registrado' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ Str::limit($cliente->direccion, 30) ?? 'No registrada' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cliente->activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="openModal({{ $cliente->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">✏️</button>
                            <button wire:click="delete({{ $cliente->id }})" wire:confirm="¿Estás seguro de eliminar este cliente?" class="text-red-600 hover:text-red-900">🗑️</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No hay clientes registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $clientes->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $editId ? '✏️ Editar Cliente' : '➕ Nuevo Cliente' }}
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" wire:model="email" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <input type="text" wire:model="telefono" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                            @error('telefono') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                            <textarea wire:model="direccion" rows="3" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                            @error('direccion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
                    <h3 class="text-lg font-bold text-gray-900">🗑️ Clientes Eliminados</h3>
                    <button wire:click="$set('showDeletedModal', false)" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div class="p-6 overflow-y-auto max-h-96">
                    @if(count($deletedItems) > 0)
                        <div class="space-y-3">
                            @foreach($deletedItems as $item)
                                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium">{{ $item->nombre }}</p>
                                        <p class="text-sm text-gray-500">{{ $item->email }}</p>
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
