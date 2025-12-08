<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Órdenes</h1>
        <div class="flex gap-2">
            <button wire:click="openDeletedModal" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">🗑️ Eliminadas</button>
            <button wire:click="openModal" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">+ Nueva Orden</button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    <div class="mb-4">
        <input wire:model.live="search" type="text" placeholder="Buscar por cliente..." class="w-full md:w-1/3 border rounded px-4 py-2">
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($ordenes as $orden)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">#{{ $orden->id }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $orden->cliente->nombre ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($orden->total, 2) }}</td>
                        <td class="px-6 py-4">
                            <select wire:change="updateEstado({{ $orden->id }}, $event.target.value)" class="text-xs rounded border px-2 py-1
                                @if($orden->estado === 'completada') bg-green-100 text-green-800
                                @elseif($orden->estado === 'procesando') bg-blue-100 text-blue-800
                                @elseif($orden->estado === 'cancelada') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                <option value="pendiente" {{ $orden->estado === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="procesando" {{ $orden->estado === 'procesando' ? 'selected' : '' }}>Procesando</option>
                                <option value="completada" {{ $orden->estado === 'completada' ? 'selected' : '' }}>Completada</option>
                                <option value="cancelada" {{ $orden->estado === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $orden->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <button wire:click="showDetail({{ $orden->id }})" class="text-green-600 hover:text-green-800 mr-2">Ver</button>
                            <button wire:click="edit({{ $orden->id }})" class="text-blue-600 hover:text-blue-800 mr-2">Editar</button>
                            <button wire:click="delete({{ $orden->id }})" wire:confirm="¿Eliminar esta orden?" class="text-red-600 hover:text-red-800">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No hay órdenes</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $ordenes->links() }}</div>

    <!-- Modal Crear/Editar -->
    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
            <h2 class="text-xl font-bold mb-4">{{ $editId ? 'Editar' : 'Nueva' }} Orden</h2>
            <form wire:submit="save">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Cliente</label>
                    <select wire:model="cliente_id" class="w-full border rounded px-3 py-2">
                        <option value="">Seleccionar cliente...</option>
                        @foreach($clientes as $cli)
                            <option value="{{ $cli->id }}">{{ $cli->nombre }} - {{ $cli->email }}</option>
                        @endforeach
                    </select>
                    @error('cliente_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Estado</label>
                    <select wire:model="estado" class="w-full border rounded px-3 py-2">
                        <option value="pendiente">Pendiente</option>
                        <option value="procesando">Procesando</option>
                        <option value="completada">Completada</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Productos</label>
                    <div class="border rounded p-3 max-h-48 overflow-y-auto">
                        @foreach($productosDisponibles as $prod)
                            <div class="flex items-center justify-between py-2 border-b">
                                <label class="flex items-center">
                                    <input type="checkbox" wire:click="toggleProducto({{ $prod->id }})" {{ in_array($prod->id, $productos_seleccionados) ? 'checked' : '' }} class="mr-2">
                                    {{ $prod->nombre }} - ${{ number_format($prod->precio, 2) }} (Stock: {{ $prod->stock }})
                                </label>
                                @if(in_array($prod->id, $productos_seleccionados))
                                    <input type="number" wire:model="cantidades.{{ $prod->id }}" min="1" max="{{ $prod->stock }}" class="w-20 border rounded px-2 py-1 text-sm">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Notas</label>
                    <textarea wire:model="notas" class="w-full border rounded px-3 py-2" rows="2"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="closeModal" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cancelar</button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Modal Detalle -->
    @if($showDetailModal && $ordenDetalle)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <h2 class="text-xl font-bold mb-4">Orden #{{ $ordenDetalle->id }}</h2>
            <div class="mb-4">
                <p><strong>Cliente:</strong> {{ $ordenDetalle->cliente->nombre ?? 'N/A' }}</p>
                <p><strong>Email:</strong> {{ $ordenDetalle->cliente->email ?? 'N/A' }}</p>
                <p><strong>Estado:</strong> {{ ucfirst($ordenDetalle->estado) }}</p>
                <p><strong>Fecha:</strong> {{ $ordenDetalle->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="mb-4">
                <h3 class="font-bold mb-2">Productos:</h3>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-1 text-left">Producto</th>
                            <th class="px-2 py-1 text-left">Cant.</th>
                            <th class="px-2 py-1 text-left">Precio</th>
                            <th class="px-2 py-1 text-left">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ordenDetalle->productos as $prod)
                            <tr>
                                <td class="px-2 py-1">{{ $prod->nombre }}</td>
                                <td class="px-2 py-1">{{ $prod->pivot->cantidad }}</td>
                                <td class="px-2 py-1">${{ number_format($prod->pivot->precio_unitario, 2) }}</td>
                                <td class="px-2 py-1">${{ number_format($prod->pivot->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-xl font-bold text-right">Total: ${{ number_format($ordenDetalle->total, 2) }}</p>
            @if($ordenDetalle->notas)
                <p class="mt-2 text-gray-600"><strong>Notas:</strong> {{ $ordenDetalle->notas }}</p>
            @endif
            <div class="mt-4 flex justify-end">
                <button wire:click="closeDetailModal" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cerrar</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Eliminados -->
    @if($showDeletedModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-96 overflow-y-auto">
            <h2 class="text-xl font-bold mb-4">Órdenes Eliminadas</h2>
            @if(count($deletedItems) > 0)
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Cliente</th>
                            <th class="px-4 py-2 text-left">Total</th>
                            <th class="px-4 py-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deletedItems as $item)
                            <tr>
                                <td class="px-4 py-2">#{{ $item->id }}</td>
                                <td class="px-4 py-2">{{ $item->cliente->nombre ?? 'N/A' }}</td>
                                <td class="px-4 py-2">${{ number_format($item->total, 2) }}</td>
                                <td class="px-4 py-2">
                                    <button wire:click="restore({{ $item->id }})" class="text-green-600 hover:text-green-800 mr-2">Restaurar</button>
                                    <button wire:click="forceDelete({{ $item->id }})" wire:confirm="¿Eliminar permanentemente?" class="text-red-600 hover:text-red-800">Eliminar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500 text-center py-4">No hay órdenes eliminadas</p>
            @endif
            <div class="mt-4 flex justify-end">
                <button wire:click="closeDeletedModal" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cerrar</button>
            </div>
        </div>
    </div>
    @endif
</div>
