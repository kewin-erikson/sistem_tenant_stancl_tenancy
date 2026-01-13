<div>
    <div class="max-w-7xl mx-auto mt-10 px-4 sm:px-6 lg:px-8">
    
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('success') }}
            
            @if(session()->has('credentials'))
                <div class="mt-3 p-3 bg-white rounded border border-green-300">
                    <p class="font-bold text-sm mb-2">🔑 Credenciales de Acceso:</p>
                    <div class="text-sm space-y-1">
                        <p><span class="font-semibold">Email:</span> <code class="bg-gray-100 px-2 py-1 rounded">{{ session('credentials')['email'] }}</code></p>
                        <p><span class="font-semibold">Password:</span> <code class="bg-gray-100 px-2 py-1 rounded">{{ session('credentials')['password'] }}</code></p>
                        <p><span class="font-semibold">URL:</span> <a href="{{ session('credentials')['url'] }}" target="_blank" class="text-blue-600 hover:underline">{{ session('credentials')['url'] }}</a></p>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Panel de Nodos -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">1. Registrar Nodo de Datos</h2>
            
            <div class="mb-3">
                <label class="block font-bold text-gray-700 text-sm">Alias del Servidor</label>
                <input wire:model="node_name" type="text" class="border border-gray-300 w-full p-2 rounded focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: AWS RDS Principal">
            </div>

            <div class="mb-3">
                <label class="block font-bold text-gray-700 text-sm">Host / IP</label>
                <input wire:model="node_host" type="text" class="border border-gray-300 w-full p-2 rounded" placeholder="127.0.0.1">
            </div>

            <div class="grid grid-cols-3 gap-2 mb-3">
                <div class="col-span-2">
                    <label class="block font-bold text-gray-700 text-sm">Usuario DB</label>
                    <input wire:model="node_user" type="text" class="border border-gray-300 w-full p-2 rounded">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 text-sm">Puerto</label>
                    <input wire:model="node_port" type="number" class="border border-gray-300 w-full p-2 rounded" placeholder="3306">
                </div>
            </div>

            <div class="mb-4">
                <label class="block font-bold text-gray-700 text-sm">Contraseña DB</label>
                <input wire:model="node_pass" type="password" class="border border-gray-300 w-full p-2 rounded">
            </div>

            <button wire:click="saveNode" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 w-full font-bold transition">
                Guardar Nodo
            </button>
        </div>

        <!-- Panel de Tenants -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4 text-blue-600 border-b pb-2">2. Registrar Nuevo Cliente</h2>

            <div class="mb-3">
                <label class="block font-bold text-gray-700 text-sm">ID del Cliente (Slug)</label>
                <input wire:model="t_id" type="text" class="border border-gray-300 w-full p-2 rounded" placeholder="Ej: nike">
                @error('t_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label class="block font-bold text-gray-700 text-sm">Subdominio</label>
                <div class="flex">
                    <input wire:model="t_domain" type="text" class="border border-gray-300 w-full p-2 rounded-l" placeholder="nike">
                    <span class="inline-flex items-center px-3 rounded-r border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                        .lvh.me
                    </span>
                </div>
                @error('t_domain') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Límite de Usuarios -->
            <div class="mb-3">
                <label class="block font-bold text-gray-700 text-sm">Límite de Usuarios</label>
                <input wire:model="t_user_limit" type="number" min="1" max="1000" class="border border-gray-300 w-full p-2 rounded" placeholder="10">
                @error('t_user_limit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                <p class="text-xs text-gray-500 mt-1">Número máximo de usuarios que puede tener este cliente</p>
            </div>

            <!-- Contraseña del Admin -->
            <div class="mb-3">
                <label class="block font-bold text-gray-700 text-sm">Contraseña del Administrador</label>
                <div class="flex gap-2">
                    <input wire:model="t_admin_password" type="text" class="border border-gray-300 w-full p-2 rounded font-mono text-sm" placeholder="Mínimo 8 caracteres">
                    <button wire:click="generatePassword" type="button" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm font-medium whitespace-nowrap">
                        🔄 Generar
                    </button>
                </div>
                @error('t_admin_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label class="block font-bold text-gray-700 text-sm">Seleccionar Nodo (Servidor)</label>
                <select wire:model="t_node_id" class="border {{ $errors->has('t_node_id') ? 'border-red-500' : 'border-gray-300' }} w-full p-2 rounded bg-white">
                    <option value="">Servidor Local (Por defecto)</option>
                    @foreach($nodes as $node)
                        <option value="{{ $node->id }}">{{ $node->name }} ({{ $node->host }})</option>
                    @endforeach
                </select>
                
                {{-- Aquí se muestra el mensaje de error --}}
                @error('t_node_id') 
                    <span class="text-red-600 text-xs font-bold mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <!-- Checkbox DB Existente y Opciones -->
            <div class="mb-4 p-3 bg-gray-50 rounded border border-gray-200">
                <div class="flex items-center mb-2">
                    <input id="existing_db" type="checkbox" wire:model.live="t_existing" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                    <label for="existing_db" class="ml-2 text-sm font-medium text-gray-900">¿Usar base de datos existente?</label>
                </div>

                @if($t_existing)
                    <div class="mt-2">
                        <label class="block font-bold text-yellow-700 text-sm">Nombre EXACTO de la Base de Datos</label>
                        <input wire:model="t_db" type="text" class="border border-yellow-400 w-full p-2 rounded focus:ring-yellow-500 focus:border-yellow-500" placeholder="Ej: db_legado_nike">
                        @error('t_db') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Checkboxes para Migraciones y Seeders (solo si DB existente) -->
                    <div class="mt-3 pt-3 border-t border-gray-300 space-y-2">
                        <p class="text-xs font-semibold text-gray-700 mb-2">Opciones para DB Existente:</p>
                        
                        <div class="flex items-center">
                            <input id="run_migrations" type="checkbox" wire:model="t_run_migrations" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <label for="run_migrations" class="ml-2 text-sm text-gray-700">
                                🔄 Ejecutar Migraciones (crear/actualizar tablas)
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input id="run_seeders" type="checkbox" wire:model="t_run_seeders" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <label for="run_seeders" class="ml-2 text-sm text-gray-700">
                                🌱 Ejecutar Seeders (crear usuario admin y datos demo)
                            </label>
                        </div>

                        @if($t_run_seeders)
                            <p class="text-xs text-green-600 ml-6 mt-1">
                                ✅ Se creará usuario: <code class="bg-white px-1 border border-green-200">admin@{{ $t_id ?? '...' }}.com</code>
                            </p>
                        @endif
                    </div>
                @else
                    <p class="text-xs text-gray-500 mt-1">
                        * Se creará una base de datos nueva automáticamente llamada: 
                        <span class="font-mono bg-gray-200 px-1">gesthor_{{ $t_id ?? '...' }}</span>
                    </p>
                    <p class="text-xs text-green-600 mt-1">
                        ✅ Se ejecutarán automáticamente migraciones y seeders
                    </p>
                    <p class="text-xs text-green-600 mt-1">
                        ✅ Se creará usuario: <code class="bg-white px-1 border border-green-200">admin@{{ $t_id ?? '...' }}.com</code>
                    </p>
                @endif
            </div>

            <button wire:click="saveTenant" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full font-bold transition shadow-lg">
                🚀 Crear y Desplegar
            </button>
        </div>
    </div>

    <!-- Lista de Tenants -->
    <div class="bg-white shadow rounded-lg mt-8 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Clientes Activos</h3>
        </div>
        <ul class="divide-y divide-gray-200">
            @forelse ($tenants as $item)
                <li class="px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-blue-600 truncate">
                                {{ $item->id }}
                            </div>
                            <div class="text-sm text-gray-500 space-x-2 mt-1">
                                <span>{{ $item->domains->first()->domain ?? 'Sin dominio' }}</span>
                                
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item->db_node ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $item->db_node->name ?? 'Local' }}
                                </span>
                                
                                @if($item->is_existing_db)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        DB Externa
                                    </span>
                                @endif
                                
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    👥 Límite: {{ $item->user_limit }}
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="http://{{ $item->domains->first()->domain }}:8000/login" target="_blank" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none transition">
                                Abrir Sitio &rarr;
                            </a>
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-6 py-4 text-center text-gray-500">
                    No hay clientes registrados aún
                </li>
            @endforelse
        </ul>
    </div>
</div>
</div>