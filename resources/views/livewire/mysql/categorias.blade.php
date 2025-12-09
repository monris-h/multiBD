<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Categorías</h1>
            <p class="text-slate-500 mt-1">Gestión de categorías en MySQL</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="openDeletedModal" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                Ver eliminadas
            </button>
            <button wire:click="openModal" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                Nueva categoría
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input wire:model.live="search" type="text" placeholder="Buscar categorías..." class="w-full md:w-1/3 pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Descripción</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Creado</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($categorias as $categoria)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $categoria->id }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $categoria->nombre }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ Str::limit($categoria->descripcion, 50) }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $categoria->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="edit({{ $categoria->id }})" class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition">Editar</button>
                            <button wire:click="delete({{ $categoria->id }})" wire:confirm="¿Eliminar esta categoría?" class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <p class="text-slate-500">No hay categorías</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $categorias->links() }}</div>

    @if($showModal)
    <div class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">{{ $editId ? 'Editar' : 'Nueva' }} categoría</h2>
            </div>
            <form wire:submit="save" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre</label>
                    <input wire:model="nombre" type="text" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    @error('nombre') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
                    <textarea wire:model="descripcion" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none"></textarea>
                    @error('descripcion') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
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
                <h2 class="text-lg font-semibold text-slate-900">Categorías eliminadas</h2>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                @if(count($deletedItems) > 0)
                    <div class="space-y-3">
                        @foreach($deletedItems as $item)
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-900">{{ $item->nombre }}</p>
                                    <p class="text-xs text-slate-500 mt-1">Eliminado: {{ $item->deleted_at->format('d/m/Y H:i') }}</p>
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
                        <p class="text-slate-500">No hay categorías eliminadas</p>
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
