<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Categorías</h1>
        <div class="flex gap-2">
            <button wire:click="openDeletedModal" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                🗑️ Eliminados
            </button>
            <button wire:click="openModal" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                + Nueva Categoría
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-4">
        <input wire:model.live="search" type="text" placeholder="Buscar categorías..." 
               class="w-full md:w-1/3 border rounded px-4 py-2">
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Creado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($categorias as $categoria)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $categoria->id }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $categoria->nombre }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($categoria->descripcion, 50) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $categoria->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <button wire:click="edit({{ $categoria->id }})" class="text-blue-600 hover:text-blue-800 mr-2">Editar</button>
                            <button wire:click="delete({{ $categoria->id }})" wire:confirm="¿Eliminar esta categoría?" class="text-red-600 hover:text-red-800">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No hay categorías</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $categorias->links() }}</div>

    <!-- Modal Crear/Editar -->
    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">{{ $editId ? 'Editar' : 'Nueva' }} Categoría</h2>
            <form wire:submit="save">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nombre</label>
                    <input wire:model="nombre" type="text" class="w-full border rounded px-3 py-2 @error('nombre') border-red-500 @enderror">
                    @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Descripción</label>
                    <textarea wire:model="descripcion" class="w-full border rounded px-3 py-2" rows="3"></textarea>
                    @error('descripcion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="closeModal" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cancelar</button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Modal Eliminados -->
    @if($showDeletedModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-96 overflow-y-auto">
            <h2 class="text-xl font-bold mb-4">Categorías Eliminadas</h2>
            @if(count($deletedItems) > 0)
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Nombre</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Eliminado</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deletedItems as $item)
                            <tr>
                                <td class="px-4 py-2">{{ $item->nombre }}</td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ $item->deleted_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2">
                                    <button wire:click="restore({{ $item->id }})" class="text-green-600 hover:text-green-800 mr-2">Restaurar</button>
                                    <button wire:click="forceDelete({{ $item->id }})" wire:confirm="¿Eliminar permanentemente?" class="text-red-600 hover:text-red-800">Eliminar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500 text-center py-4">No hay categorías eliminadas</p>
            @endif
            <div class="mt-4 flex justify-end">
                <button wire:click="closeDeletedModal" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cerrar</button>
            </div>
        </div>
    </div>
    @endif
</div>
