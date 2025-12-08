<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Productos</h1>
        <div class="flex gap-2">
            <button wire:click="openDeletedModal" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">🗑️ Eliminados</button>
            <button wire:click="openModal" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">+ Nuevo Producto</button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('message') }}</div>
    @endif

    <div class="mb-4">
        <input wire:model.live="search" type="text" placeholder="Buscar productos..." class="w-full md:w-1/3 border rounded px-4 py-2">
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($productos as $producto)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $producto->nombre }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $producto->categoria->nombre ?? 'Sin categoría' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($producto->precio, 2) }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 rounded {{ $producto->stock > 10 ? 'bg-green-100 text-green-800' : ($producto->stock > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $producto->stock }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <button wire:click="edit({{ $producto->id }})" class="text-blue-600 hover:text-blue-800 mr-2">Editar</button>
                            <button wire:click="delete({{ $producto->id }})" wire:confirm="¿Eliminar este producto?" class="text-red-600 hover:text-red-800">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No hay productos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $productos->links() }}</div>

    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">{{ $editId ? 'Editar' : 'Nuevo' }} Producto</h2>
            <form wire:submit="save">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nombre</label>
                    <input wire:model="nombre" type="text" class="w-full border rounded px-3 py-2">
                    @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Categoría</label>
                    <select wire:model="categoria_id" class="w-full border rounded px-3 py-2">
                        <option value="">Seleccionar...</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Precio</label>
                        <input wire:model="precio" type="number" step="0.01" class="w-full border rounded px-3 py-2">
                        @error('precio') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Stock</label>
                        <input wire:model="stock" type="number" class="w-full border rounded px-3 py-2">
                        @error('stock') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Descripción</label>
                    <textarea wire:model="descripcion" class="w-full border rounded px-3 py-2" rows="2"></textarea>
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
            <h2 class="text-xl font-bold mb-4">Productos Eliminados</h2>
            @if(count($deletedItems) > 0)
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Nombre</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Categoría</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deletedItems as $item)
                            <tr>
                                <td class="px-4 py-2">{{ $item->nombre }}</td>
                                <td class="px-4 py-2">{{ $item->categoria->nombre ?? 'N/A' }}</td>
                                <td class="px-4 py-2">
                                    <button wire:click="restore({{ $item->id }})" class="text-green-600 hover:text-green-800 mr-2">Restaurar</button>
                                    <button wire:click="forceDelete({{ $item->id }})" wire:confirm="¿Eliminar permanentemente?" class="text-red-600 hover:text-red-800">Eliminar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500 text-center py-4">No hay productos eliminados</p>
            @endif
            <div class="mt-4 flex justify-end">
                <button wire:click="closeDeletedModal" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cerrar</button>
            </div>
        </div>
    </div>
    @endif
</div>
