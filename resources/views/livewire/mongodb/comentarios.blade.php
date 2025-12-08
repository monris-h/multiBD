<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">💬 Comentarios (MongoDB)</h1>
        <div class="flex gap-2">
            <button wire:click="openDeletedModal" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">🗑️ Eliminados</button>
            <button wire:click="openModal" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">+ Nuevo Comentario</button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('message') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <input wire:model.live="search" type="text" placeholder="Buscar comentarios..." class="border rounded px-4 py-2">
        <select wire:model.live="filterEntidad" class="border rounded px-4 py-2">
            <option value="">Todas las entidades</option>
            <option value="producto">Producto</option>
            <option value="categoria">Categoría</option>
            <option value="cliente">Cliente</option>
            <option value="orden">Orden</option>
        </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($comentarios as $comentario)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-start mb-2">
                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">{{ $comentario->entidad_tipo }}</span>
                    <div class="text-yellow-400">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $comentario->calificacion) ★ @else ☆ @endif
                        @endfor
                    </div>
                </div>
                <p class="text-gray-800 mb-2">{{ $comentario->contenido }}</p>
                <div class="text-sm text-gray-500 mb-2">
                    <p><strong>Usuario:</strong> {{ $comentario->usuario }}</p>
                    <p><strong>Entidad ID:</strong> {{ $comentario->entidad_id }}</p>
                    <p><strong>Fecha:</strong> {{ $comentario->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex justify-end gap-2">
                    <button wire:click="edit('{{ $comentario->_id }}')" class="text-blue-600 hover:text-blue-800 text-sm">Editar</button>
                    <button wire:click="delete('{{ $comentario->_id }}')" wire:confirm="¿Eliminar este comentario?" class="text-red-600 hover:text-red-800 text-sm">Eliminar</button>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center text-gray-500 py-8">No hay comentarios</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $comentarios->links() }}</div>

    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">{{ $editId ? 'Editar' : 'Nuevo' }} Comentario</h2>
            <form wire:submit="save">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Tipo de Entidad</label>
                    <select wire:model="entidad_tipo" class="w-full border rounded px-3 py-2">
                        <option value="">Seleccionar...</option>
                        <option value="producto">Producto</option>
                        <option value="categoria">Categoría</option>
                        <option value="cliente">Cliente</option>
                        <option value="orden">Orden</option>
                    </select>
                    @error('entidad_tipo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">ID de Entidad</label>
                    <input wire:model="entidad_id" type="text" class="w-full border rounded px-3 py-2">
                    @error('entidad_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Usuario</label>
                    <input wire:model="usuario" type="text" class="w-full border rounded px-3 py-2">
                    @error('usuario') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Contenido</label>
                    <textarea wire:model="contenido" class="w-full border rounded px-3 py-2" rows="3"></textarea>
                    @error('contenido') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Calificación: {{ $calificacion }} ⭐</label>
                    <input wire:model="calificacion" type="range" min="1" max="5" class="w-full">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="closeModal" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cancelar</button>
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($showDeletedModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-96 overflow-y-auto">
            <h2 class="text-xl font-bold mb-4">Comentarios Eliminados</h2>
            @if(count($deletedItems) > 0)
                <div class="space-y-2">
                    @foreach($deletedItems as $item)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                            <div>
                                <p class="font-medium">{{ Str::limit($item->contenido, 50) }}</p>
                                <p class="text-sm text-gray-500">{{ $item->usuario }} - {{ $item->entidad_tipo }}</p>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="restore('{{ $item->_id }}')" class="text-green-600 hover:text-green-800">Restaurar</button>
                                <button wire:click="forceDelete('{{ $item->_id }}')" wire:confirm="¿Eliminar permanentemente?" class="text-red-600 hover:text-red-800">Eliminar</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No hay comentarios eliminados</p>
            @endif
            <div class="mt-4 flex justify-end">
                <button wire:click="closeDeletedModal" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cerrar</button>
            </div>
        </div>
    </div>
    @endif
</div>
