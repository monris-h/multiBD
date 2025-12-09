<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Comentarios</h1>
            <p class="text-slate-500 mt-1">Gestión de comentarios en MongoDB</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="openDeletedModal" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                Ver eliminados
            </button>
            <button wire:click="openModal" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">
                Nuevo comentario
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input wire:model.live="search" type="text" placeholder="Buscar comentarios..." class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
            </div>
            <select wire:model.live="filterEntidad" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                <option value="">Todas las entidades</option>
                <option value="producto">Producto</option>
                <option value="categoria">Categoría</option>
                <option value="cliente">Cliente</option>
                <option value="orden">Orden</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($comentarios as $comentario)
            <div class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md transition">
                <div class="flex items-start justify-between mb-3">
                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-emerald-50 text-emerald-700">{{ $comentario->entidad_tipo }}</span>
                    <div class="flex gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4 {{ $i <= $comentario->calificacion ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                            </svg>
                        @endfor
                    </div>
                </div>
                <p class="text-slate-700 text-sm mb-4 line-clamp-3">{{ $comentario->contenido }}</p>
                <div class="text-xs text-slate-500 space-y-1 mb-4">
                    <p><span class="font-medium text-slate-600">Usuario:</span> {{ $comentario->usuario }}</p>
                    <p><span class="font-medium text-slate-600">Entidad ID:</span> {{ $comentario->entidad_id }}</p>
                    <p><span class="font-medium text-slate-600">Fecha:</span> {{ $comentario->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button wire:click="edit('{{ $comentario->_id }}')" class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition">
                        Editar
                    </button>
                    <button wire:click="delete('{{ $comentario->_id }}')" wire:confirm="¿Estás seguro de eliminar este comentario?" class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition">
                        Eliminar
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-xl border border-slate-200 py-12">
                <div class="text-center">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                    <p class="text-slate-500">No hay comentarios</p>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $comentarios->links() }}</div>

    <!-- Modal Crear/Editar -->
    @if($showModal)
    <div class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md" @click.away="$wire.closeModal()">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">{{ $editId ? 'Editar' : 'Nuevo' }} comentario</h2>
            </div>
            <form wire:submit="save" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de entidad</label>
                    <select wire:model="entidad_tipo" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                        <option value="">Seleccionar...</option>
                        <option value="producto">Producto</option>
                        <option value="categoria">Categoría</option>
                        <option value="cliente">Cliente</option>
                        <option value="orden">Orden</option>
                    </select>
                    @error('entidad_tipo') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">ID de entidad</label>
                    <input wire:model="entidad_id" type="text" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                    @error('entidad_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Usuario</label>
                    <input wire:model="usuario" type="text" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                    @error('usuario') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Contenido</label>
                    <textarea wire:model="contenido" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition resize-none"></textarea>
                    @error('contenido') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Calificación: {{ $calificacion }}</label>
                    <div class="flex gap-2">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" wire:click="$set('calificacion', {{ $i }})" class="focus:outline-none">
                                <svg class="w-8 h-8 {{ $i <= $calificacion ? 'text-amber-400' : 'text-slate-200' }} hover:text-amber-400 transition" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                                </svg>
                            </button>
                        @endfor
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Modal Eliminados -->
    @if($showDeletedModal)
    <div class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[80vh] flex flex-col">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">Comentarios eliminados</h2>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                @if(count($deletedItems) > 0)
                    <div class="space-y-3">
                        @foreach($deletedItems as $item)
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-slate-900 truncate">{{ Str::limit($item->contenido, 60) }}</p>
                                    <p class="text-xs text-slate-500 mt-1">{{ $item->usuario }} · {{ $item->entidad_tipo }}</p>
                                </div>
                                <div class="flex gap-2 ml-4">
                                    <button wire:click="restore('{{ $item->_id }}')" class="px-3 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50 rounded-lg transition">
                                        Restaurar
                                    </button>
                                    <button wire:click="forceDelete('{{ $item->_id }}')" wire:confirm="¿Eliminar permanentemente?" class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        <p class="text-slate-500">No hay comentarios eliminados</p>
                    </div>
                @endif
            </div>
            <div class="px-6 py-4 border-t border-slate-200">
                <button wire:click="closeDeletedModal" class="w-full px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
