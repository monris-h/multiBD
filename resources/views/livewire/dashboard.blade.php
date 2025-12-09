<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">Dashboard</h1>
        <p class="text-slate-500 mt-1">Resumen general del sistema multi-base de datos</p>
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6">
            {{ session('message') }}
        </div>
    @endif

    <!-- MySQL Stats -->
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-4">
            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
            <h2 class="text-lg font-medium text-slate-800">MySQL</h2>
            <span class="text-xs text-slate-400 font-normal">Base de datos relacional</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="/categorias" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-blue-300 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Categorías</p>
                        <p class="text-2xl font-semibold text-slate-900 mt-1">{{ $mysqlStats['categorias'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center group-hover:bg-blue-100 transition">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </a>
            <a href="/productos" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-blue-300 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Productos</p>
                        <p class="text-2xl font-semibold text-slate-900 mt-1">{{ $mysqlStats['productos'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center group-hover:bg-blue-100 transition">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
            </a>
            <a href="/clientes" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-blue-300 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Clientes</p>
                        <p class="text-2xl font-semibold text-slate-900 mt-1">{{ $mysqlStats['clientes'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center group-hover:bg-blue-100 transition">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </a>
            <a href="/ordenes" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-blue-300 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Órdenes</p>
                        <p class="text-2xl font-semibold text-slate-900 mt-1">{{ $mysqlStats['ordenes'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center group-hover:bg-blue-100 transition">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- MongoDB Stats -->
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-4">
            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
            <h2 class="text-lg font-medium text-slate-800">MongoDB</h2>
            <span class="text-xs text-slate-400 font-normal">Base de datos NoSQL</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="/logs" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-emerald-300 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Logs</p>
                        <p class="text-2xl font-semibold text-slate-900 mt-1">{{ $mongoStats['logs'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center group-hover:bg-emerald-100 transition">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </a>
            <a href="/comentarios" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-emerald-300 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Comentarios</p>
                        <p class="text-2xl font-semibold text-slate-900 mt-1">{{ $mongoStats['comentarios'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center group-hover:bg-emerald-100 transition">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                    </div>
                </div>
            </a>
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Calificación promedio</p>
                        <p class="text-2xl font-semibold text-slate-900 mt-1">{{ number_format($mongoStats['calificacion_promedio'], 1) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Redis Stats -->
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-4">
            <span class="w-3 h-3 rounded-full bg-red-500"></span>
            <h2 class="text-lg font-medium text-slate-800">Redis</h2>
            <span class="text-xs text-slate-400 font-normal">Almacenamiento clave-valor</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="/configuraciones" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-red-300 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Configuraciones</p>
                        <p class="text-2xl font-semibold text-slate-900 mt-1">{{ $redisStats['configuraciones'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center group-hover:bg-red-100 transition">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>
            </a>
            <a href="/sesiones" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-red-300 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Sesiones activas</p>
                        <p class="text-2xl font-semibold text-slate-900 mt-1">{{ $redisStats['sesiones'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center group-hover:bg-red-100 transition">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Actividad Reciente -->
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-200">
            <h2 class="text-lg font-medium text-slate-800">Actividad reciente</h2>
        </div>
        @if(count($recentLogs) > 0)
            <div class="divide-y divide-slate-100">
                @foreach($recentLogs as $log)
                    <div class="px-6 py-4 flex items-center gap-4">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center
                            @if($log->accion === 'crear') bg-emerald-100 text-emerald-600
                            @elseif($log->accion === 'actualizar') bg-amber-100 text-amber-600
                            @elseif($log->accion === 'eliminar') bg-red-100 text-red-600
                            @else bg-slate-100 text-slate-600 @endif">
                            @if($log->accion === 'crear')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            @elseif($log->accion === 'actualizar')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            @elseif($log->accion === 'eliminar')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-900">
                                <span class="font-medium capitalize">{{ $log->accion }}</span>
                                en <span class="font-medium">{{ $log->entidad }}</span>
                            </p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $log->usuario ?? 'Sistema' }}</p>
                        </div>
                        <div class="text-xs text-slate-400">
                            {{ $log->created_at->diffForHumans() }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-slate-500">No hay actividad reciente</p>
            </div>
        @endif
    </div>
</div>