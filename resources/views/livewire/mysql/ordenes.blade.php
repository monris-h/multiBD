<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Órdenes</h1>
            <p class="text-slate-500 mt-1">Gestión de órdenes en MySQL</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="openDeletedModal" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">Ver eliminadas</button>
            <button wire:click="openModal" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">Nueva orden</button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input wire:model.live="search" type="text" placeholder="Buscar por cliente..." class="w-full md:w-1/3 pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Fecha</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($ordenes as $orden)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 text-sm text-slate-500">#{{ $orden->id }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $orden->cliente->nombre ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-900">${{ number_format($orden->total, 2) }}</td>
                        <td class="px-6 py-4">
                            <select wire:change="updateEstado({{ $orden->id }}, $event.target.value)" class="text-xs font-medium rounded-lg border-0 px-2.5 py-1.5
                                @if($orden->estado === 'completada') bg-emerald-50 text-emerald-700
                                @elseif($orden->estado === 'procesando') bg-blue-50 text-blue-700
                                @elseif($orden->estado === 'cancelada') bg-red-50 text-red-700
                                @else bg-amber-50 text-amber-700 @endif">
                                <option value="pendiente" {{ $orden->estado === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="procesando" {{ $orden->estado === 'procesando' ? 'selected' : '' }}>Procesando</option>
                                <option value="completada" {{ $orden->estado === 'completada' ? 'selected' : '' }}>Completada</option>
                                <option value="cancelada" {{ $orden->estado === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $orden->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="showDetail({{ $orden->id }})" class="px-3 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50 rounded-lg transition">Ver</button>
                            <button wire:click="edit({{ $orden->id }})" class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition">Editar</button>
                            <button wire:click="delete({{ $orden->id }})" wire:confirm="¿Eliminar esta orden?" class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            <p class="text-slate-500">No hay órdenes</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $ordenes->links() }}</div>

    <!-- Modal Crear/Editar -->
    @if($showModal)
    <div class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">{{ $editId ? 'Editar' : 'Nueva' }} orden</h2>
            </div>
            <form wire:submit="save" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Cliente</label>
                    <select wire:model="cliente_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        <option value="">Seleccionar cliente...</option>
                        @foreach($clientes as $cli)
                            <option value="{{ $cli->id }}">{{ $cli->nombre }} - {{ $cli->email }}</option>
                        @endforeach
                    </select>
                    @error('cliente_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Estado</label>
                    <select wire:model="estado" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        <option value="pendiente">Pendiente</option>
                        <option value="procesando">Procesando</option>
                        <option value="completada">Completada</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Productos</label>
                    <div class="border border-slate-200 rounded-lg p-3 max-h-48 overflow-y-auto space-y-2">
                        @foreach($productosDisponibles as $prod)
                            <div class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-slate-50">
                                <label class="flex items-center flex-1 cursor-pointer">
                                    <input type="checkbox" wire:click="toggleProducto({{ $prod->id }})" {{ in_array($prod->id, $productos_seleccionados) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                                    <span class="ml-3 text-sm text-slate-700">{{ $prod->nombre }} - ${{ number_format($prod->precio, 2) }} <span class="text-slate-400">(Stock: {{ $prod->stock }})</span></span>
                                </label>
                                @if(in_array($prod->id, $productos_seleccionados))
                                    <input type="number" wire:model="cantidades.{{ $prod->id }}" min="1" max="{{ $prod->stock }}" class="w-20 px-2 py-1 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Notas</label>
                    <textarea wire:model="notas" rows="2" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Modal Detalle -->
    @if($showDetailModal && $ordenDetalle)
    <div class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">Orden #{{ $ordenDetalle->id }}</h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-slate-500">Cliente:</span> <span class="font-medium text-slate-900">{{ $ordenDetalle->cliente->nombre ?? 'N/A' }}</span></div>
                    <div><span class="text-slate-500">Email:</span> <span class="font-medium text-slate-900">{{ $ordenDetalle->cliente->email ?? 'N/A' }}</span></div>
                    <div><span class="text-slate-500">Estado:</span> <span class="font-medium text-slate-900">{{ ucfirst($ordenDetalle->estado) }}</span></div>
                    <div><span class="text-slate-500">Fecha:</span> <span class="font-medium text-slate-900">{{ $ordenDetalle->created_at->format('d/m/Y H:i') }}</span></div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 mb-3">Productos</h3>
                    <div class="bg-slate-50 rounded-xl overflow-hidden">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600">Producto</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold text-slate-600">Cant.</th>
                                    <th class="px-4 py-2 text-right text-xs font-semibold text-slate-600">Precio</th>
                                    <th class="px-4 py-2 text-right text-xs font-semibold text-slate-600">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($ordenDetalle->productos as $prod)
                                    <tr>
                                        <td class="px-4 py-2 text-slate-700">{{ $prod->nombre }}</td>
                                        <td class="px-4 py-2 text-center text-slate-600">{{ $prod->pivot->cantidad }}</td>
                                        <td class="px-4 py-2 text-right text-slate-600">${{ number_format($prod->pivot->precio_unitario, 2) }}</td>
                                        <td class="px-4 py-2 text-right font-medium text-slate-900">${{ number_format($prod->pivot->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-100">
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-right font-semibold text-slate-700">Total:</td>
                                    <td class="px-4 py-2 text-right font-bold text-slate-900">${{ number_format($ordenDetalle->total, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                @if($ordenDetalle->notas)
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 mb-2">Notas</h3>
                    <p class="text-sm text-slate-600 bg-slate-50 rounded-lg p-3">{{ $ordenDetalle->notas }}</p>
                </div>
                @endif
            </div>
            <div class="px-6 py-4 border-t border-slate-200">
                <button wire:click="closeDetailModal" class="w-full px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">Cerrar</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Eliminados -->
    @if($showDeletedModal)
    <div class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[80vh] flex flex-col">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">Órdenes eliminadas</h2>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                @if(count($deletedItems) > 0)
                    <div class="space-y-3">
                        @foreach($deletedItems as $item)
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-900">Orden #{{ $item->id }} - {{ $item->cliente->nombre ?? 'N/A' }}</p>
                                    <p class="text-xs text-slate-500 mt-1">${{ number_format($item->total, 2) }} - {{ ucfirst($item->estado) }}</p>
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
                        <p class="text-slate-500">No hay órdenes eliminadas</p>
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
