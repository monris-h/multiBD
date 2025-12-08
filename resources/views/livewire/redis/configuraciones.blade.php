<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">⚙️ Configuraciones (Redis)</h1>
        <div class="flex gap-2">
            <button wire:click="openDeletedModal" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">🗑️ Eliminadas</button>
            <button wire:click="openModal" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">+ Nueva Configuración</button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('message') }}</div>
    @endif

    <div class="mb-4">
        <input wire:model.live="search" type="text" placeholder="Buscar configuraciones..." class="w-full md:w-1/3 border rounded px-4 py-2">
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clave</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($configuraciones as $clave => $config)
                    <tr>
                        <td class="px-6 py-4 text-sm font-mono font-medium text-gray-900">{{ $clave }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if(is_array($config['valor'] ?? null))
                                <pre class="text-xs bg-gray-100 p-1 rounded">{{ json_encode($config['valor'], JSON_PRETTY_PRINT) }}</pre>
                            @else
                                {{ Str::limit($config['valor'] ?? 'N/A', 50) }}
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded 
                                @if(($config['tipo'] ?? 'string') === 'string') bg-blue-100 text-blue-800
                                @elseif(($config['tipo'] ?? '') === 'integer') bg-green-100 text-green-800
                                @elseif(($config['tipo'] ?? '') === 'boolean') bg-yellow-100 text-yellow-800
                                @else bg-purple-100 text-purple-800 @endif">
                                {{ $config['tipo'] ?? 'string' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($config['descripcion'] ?? '-', 30) }}</td>
                        <td class="px-6 py-4 text-sm">
                            <button wire:click="edit('{{ $clave }}')" class="text-blue-600 hover:text-blue-800 mr-2">Editar</button>
                            <button wire:click="delete('{{ $clave }}')" wire:confirm="¿Eliminar esta configuración?" class="text-red-600 hover:text-red-800">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No hay configuraciones</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">{{ $editKey ? 'Editar' : 'Nueva' }} Configuración</h2>
            <form wire:submit="save">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Clave</label>
                    <input wire:model="clave" type="text" class="w-full border rounded px-3 py-2 font-mono" {{ $editKey ? 'disabled' : '' }}>
                    @error('clave') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Tipo</label>
                    <select wire:model="tipo" class="w-full border rounded px-3 py-2">
                        <option value="string">String</option>
                        <option value="integer">Integer</option>
                        <option value="boolean">Boolean</option>
                        <option value="json">JSON</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Valor</label>
                    @if($tipo === 'boolean')
                        <select wire:model="valor" class="w-full border rounded px-3 py-2">
                            <option value="true">true</option>
                            <option value="false">false</option>
                        </select>
                    @elseif($tipo === 'json')
                        <textarea wire:model="valor" class="w-full border rounded px-3 py-2 font-mono" rows="4" placeholder='{"key": "value"}'></textarea>
                    @else
                        <input wire:model="valor" type="{{ $tipo === 'integer' ? 'number' : 'text' }}" class="w-full border rounded px-3 py-2">
                    @endif
                    @error('valor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Descripción</label>
                    <textarea wire:model="descripcion" class="w-full border rounded px-3 py-2" rows="2"></textarea>
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
            <h2 class="text-xl font-bold mb-4">Configuraciones Eliminadas</h2>
            @if(count($deletedItems) > 0)
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Clave</th>
                            <th class="px-4 py-2 text-left">Valor</th>
                            <th class="px-4 py-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deletedItems as $clave => $item)
                            <tr>
                                <td class="px-4 py-2 font-mono">{{ $clave }}</td>
                                <td class="px-4 py-2">{{ Str::limit(is_array($item['valor'] ?? null) ? json_encode($item['valor']) : $item['valor'] ?? '', 30) }}</td>
                                <td class="px-4 py-2">
                                    <button wire:click="restore('{{ $clave }}')" class="text-green-600 hover:text-green-800">Restaurar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500 text-center py-4">No hay configuraciones eliminadas</p>
            @endif
            <div class="mt-4 flex justify-end">
                <button wire:click="closeDeletedModal" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cerrar</button>
            </div>
        </div>
    </div>
    @endif
</div>
