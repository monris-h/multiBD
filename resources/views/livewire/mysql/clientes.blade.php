<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Clientes</h1>
        <div class="flex gap-2">
            <button wire:click="openDeletedModal" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">🗑️ Eliminados</button>
            <button wire:click="openModal" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">+ Nuevo Cliente</button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('message') }}</div>
    @endif

    <div class="mb-4">
        <input wire:model.live="search" type="text" placeholder="Buscar clientes..." class="w-full md:w-1/3 border rounded px-4 py-2">
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dirección</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($clientes as $cliente)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $cliente->nombre }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $cliente->email }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $cliente->telefono ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($cliente->direccion, 30) ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm">
                            <button wire:click="edit({{ $cliente->id }})" class="text-blue-600 hover:text-blue-800 mr-2">Editar</button>
                            <button wire:click="delete({{ $cliente->id }})" wire:confirm="¿Eliminar este cliente?" class="text-red-600 hover:text-red-800">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No hay clientes</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $clientes->links() }}</div>

    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">{{ $editId ? 'Editar' : 'Nuevo' }} Cliente</h2>
            <form wire:submit="save">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nombre</label>
                    <input wire:model="nombre" type="text" class="w-full border rounded px-3 py-2">
                    @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input wire:model="email" type="email" class="w-full border rounded px-3 py-2">
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Teléfono</label>
                    <input wire:model="telefono" type="text" class="w-full border rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Dirección</label>
                    <textarea wire:model="direccion" class="w-full border rounded px-3 py-2" rows="2"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="closeModal" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cancelar</button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($showDeletedModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-96 overflow-y-auto">
            <h2 class="text-xl font-bold mb-4">Clientes Eliminados</h2>
            @if(count($deletedItems) > 0)
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Nombre</th>
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deletedItems as $item)
                            <tr>
                                <td class="px-4 py-2">{{ $item->nombre }}</td>
                                <td class="px-4 py-2">{{ $item->email }}</td>
                                <td class="px-4 py-2">
                                    <button wire:click="restore({{ $item->id }})" class="text-green-600 hover:text-green-800 mr-2">Restaurar</button>
                                    <button wire:click="forceDelete({{ $item->id }})" wire:confirm="¿Eliminar permanentemente?" class="text-red-600 hover:text-red-800">Eliminar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500 text-center py-4">No hay clientes eliminados</p>
            @endif
            <div class="mt-4 flex justify-end">
                <button wire:click="closeDeletedModal" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cerrar</button>
            </div>
        </div>
    </div>
    @endif
</div>
