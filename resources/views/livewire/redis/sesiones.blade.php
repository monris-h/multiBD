<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">🔐 Sesiones (Redis)</h1>
        <div class="flex gap-2">
            <button wire:click="openDeletedModal" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">🗑️ Eliminadas</button>
            <button wire:click="openModal" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">+ Nueva Sesión</button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('message') }}</div>
    @endif

    <div class="mb-4">
        <input wire:model.live="search" type="text" placeholder="Buscar por usuario o IP..." class="w-full md:w-1/3 border rounded px-4 py-2">
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User Agent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">TTL</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($sesiones as $id => $sesion)
                    <tr>
                        <td class="px-6 py-4 text-sm font-mono text-gray-900">{{ Str::limit($id, 20) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $sesion['usuario_id'] ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $sesion['ip_address'] ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($sesion['user_agent'] ?? '-', 30) }}</td>
                        <td class="px-6 py-4">
                            @if(isset($sesion['ttl']) && $sesion['ttl'] > 0)
                                <span class="px-2 py-1 text-xs rounded {{ $sesion['ttl'] > 3600 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ gmdate('H:i:s', $sesion['ttl']) }}
                                </span>
                            @elseif(isset($sesion['ttl']) && $sesion['ttl'] == -1)
                                <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">Sin expiración</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">Expirada</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <button wire:click="edit('{{ $id }}')" class="text-blue-600 hover:text-blue-800 mr-2">Editar</button>
                            <button wire:click="delete('{{ $id }}')" wire:confirm="¿Eliminar esta sesión?" class="text-red-600 hover:text-red-800">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No hay sesiones activas</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">{{ $editId ? 'Editar' : 'Nueva' }} Sesión</h2>
            <form wire:submit="save">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Usuario ID</label>
                    <input wire:model="usuario_id" type="text" class="w-full border rounded px-3 py-2">
                    @error('usuario_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">IP Address</label>
                    <input wire:model="ip_address" type="text" class="w-full border rounded px-3 py-2" placeholder="192.168.1.1">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">User Agent</label>
                    <input wire:model="user_agent" type="text" class="w-full border rounded px-3 py-2" placeholder="Mozilla/5.0...">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Datos (JSON)</label>
                    <textarea wire:model="datos" class="w-full border rounded px-3 py-2 font-mono" rows="3" placeholder='{"key": "value"}'></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="closeModal" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cancelar</button>
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($showDeletedModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-96 overflow-y-auto">
            <h2 class="text-xl font-bold mb-4">Sesiones Eliminadas</h2>
            @if(count($deletedItems) > 0)
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Usuario</th>
                            <th class="px-4 py-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deletedItems as $id => $item)
                            <tr>
                                <td class="px-4 py-2 font-mono text-sm">{{ Str::limit($id, 20) }}</td>
                                <td class="px-4 py-2">{{ $item['usuario_id'] ?? 'N/A' }}</td>
                                <td class="px-4 py-2">
                                    <button wire:click="restore('{{ $id }}')" class="text-green-600 hover:text-green-800">Restaurar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500 text-center py-4">No hay sesiones eliminadas</p>
            @endif
            <div class="mt-4 flex justify-end">
                <button wire:click="closeDeletedModal" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cerrar</button>
            </div>
        </div>
    </div>
    @endif
</div>
