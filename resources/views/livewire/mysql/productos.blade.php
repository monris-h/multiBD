<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Productos</h1>
            <p class="text-slate-500 mt-1">Gestión de productos en MySQL</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="openDeletedModal" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">Ver eliminados</button>
            <button wire:click="openModal" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">Nuevo producto</button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6">{{ session('message') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input wire:model.live="search" type="text" placeholder="Buscar productos..." class="w-full md:w-1/3 pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Categoría</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Precio</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($productos as $producto)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $producto->nombre }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $producto->categoria->nombre ?? 'Sin categoría' }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-900">${{ number_format($producto->precio, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $producto->stock > 10 ? 'bg-emerald-50 text-emerald-700' : ($producto->stock > 0 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                                {{ $producto->stock }} uds
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="edit({{ $producto->id }})" class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition">Editar</button>
                            <button wire:click="delete({{ $producto->id }})" wire:confirm="¿Eliminar este producto?" class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <p class="text-slate-500">No hay productos</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $productos->links() }}</div>

    @if($showModal)
    <div class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">{{ $editId ? 'Editar' : 'Nuevo' }} producto</h2>
            </div>
            <form wire:submit="save" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre</label>
                    <input wire:model="nombre" type="text" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    @error('nombre') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Categoría</label>
                    <select wire:model="categoria_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        <option value="">Seleccionar...</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Precio</label>
                        <input wire:model="precio" type="number" step="0.01" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        @error('precio') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Stock</label>
                        <input wire:model="stock" type="number" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        @error('stock') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
                    <textarea wire:model="descripcion" rows="2" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($showDeletedModal)
    <div class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[80vh] flex flex-col">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">Productos eliminados</h2>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                @if(count($deletedItems) > 0)
                    <div class="space-y-3">
                        @foreach($deletedItems as $item)
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-900">{{ $item->nombre }}</p>
                                    <p class="text-xs text-slate-500 mt-1">{{ $item->categoria->nombre ?? 'N/A' }} - ${{ number_format($item->precio, 2) }}</p>
                                </div>
                                <div class="flex gap-2 ml-4">
                                    <button wire:click="restore({{ $item->id }})" class="px-3 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50 rounded-lg transition">Restaurar</button>
                                    <button wire:click="forceDelete({{ $item->id }})" wire:confirm="¿Eliminar permanentemente?" class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition">Eliminar</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        <p class="text-slate-500">No hay productos eliminados</p>
                    </div>
                @endif
            </div>
            <div class="px-6 py-4 border-t border-slate-200">
                <button wire:click="closeDeletedModal" class="w-full px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">Cerrar</button>
            </div>
        </div>
    </div>
    @endif
</div>
