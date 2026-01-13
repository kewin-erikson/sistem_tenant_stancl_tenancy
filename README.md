# 📚 Sistema Multi-Tenant con Bases de Datos Externas en Laravel

## 🎯 Descripción General

Este sistema permite crear tenants (clientes) en Laravel utilizando el paquete `stancl/tenancy`, con la capacidad de:

- ✅ Conectar bases de datos en **servidores remotos** (AWS RDS, DigitalOcean, Hostinger, etc.)
- ✅ Usar bases de datos **locales**
- ✅ Trabajar con bases de datos **existentes** o crear **nuevas**
- ✅ Controlar límite de usuarios por tenant
- ✅ Ejecutar migraciones y seeders de forma condicional

---

## 📋 Requisitos Previos

- PHP 8.1+
- Laravel 11+
- MySQL/MariaDB
- Composer
- Node.js y NPM (para Livewire)

---

## 🚀 Instalación

### 1. Instalar el paquete Tenancy
documentacion oficial
[text](https://tenancyforlaravel.com/docs/v3/installation)
```bash
composer require stancl/tenancy
```

### 2. Publicar archivos de configuración

```bash
php artisan tenancy:install
```
### 2.1 Configuracion  env
Luego agregue el proveedor de servicios a su bootstrap/providers.php 
```bash
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\TenancyServiceProvider::class, // <-- here
];
```
### 2.2 Configuracion  env
    esta es la configuracion para db  
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=db_central
    DB_USERNAME=root
    DB_PASSWORD=

//para configurar el subdominio 
    CENTRAL_DOMAIN=lvh.me 

    SESSION_DRIVER=file //para activar sessiones  por archivos

### 3. Crear migraciones personalizadas

#### a) Migración para tabla `db_nodes`

```bash
php artisan make:migration create_db_nodes_table

```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
    {
        Schema::create('db_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host');
            $table->string('username');
            $table->string('password')->nullable();
            $table->integer('port')->default(3306);
             $table->boolean('is_active')->default(true); // Para mantenimiento
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('db_nodes');
    }
};

```

#### b) Migración para agregar campos a `tenants`

```bash
php artisan make:migration add_custom_fields_to_tenants_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('db_node_id')->nullable()->after('id')->constrained('db_nodes')->onDelete('set null');
            $table->boolean('is_existing_db')->default(false)->after('db_node_id');
            $table->integer('user_limit')->default(10)->after('is_existing_db');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['db_node_id']);
            $table->dropColumn(['db_node_id', 'is_existing_db', 'user_limit']);
        });
    }
};
```

#### Nota ####
Antes de  crear las migraciones 
tener encuenta 
1. Crear en la carpeta de "migrations" database\migrations la carpeta tenant Aqui dejaras las migraciones que quieres que se ejecuten  en  cada tenant
2. Ya que usaremos secciones por  archivos tendremos que   borrar  de  la migracion de users  la parte donde crea dicha tabla  Schema::create('sessions', function (Blueprint $table) {}


---
### 4. Ejecutar migraciones

```bash
php artisan migrate
```

## 🗄️ Configuración de Base de Datos

### Archivo: `config/database.php`

Agregar estas dos conexiones al array `connections`:

```php
'connections' => [
    // ... otras conexiones existentes

    // 1. PLANTILLA: Define la estructura base para conexiones remotas
    'tenant_template' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1', 
        'port' => '3306',
        'database' => 'mysql', // DB temporal para conexión inicial
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
    ],

    // 2. CONEXIÓN DINÁMICA: Aquí se inyectan las credenciales en tiempo real
    'tenant' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'database' => null,
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
    ],
],
```

---

## ⚙️ Configuración de Tenancy

### Archivo: `config/tenancy.php`

**Modificar estas secciones clave:**

```php
return [
    'tenant_model' => \App\Models\Tenant::class,//ruta del modelo prsonalizado de Tennat
    
    'central_domains' => [
        '127.0.0.1',
        'localhost',
        // Agregar tu dominio en producción
    ],

    'bootstrappers' => [
        // ❌ COMENTAR O ELIMINAR ESTA LÍNEA:
        // Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
        
        // ✅ USAR TU BOOTSTRAPPER PERSONALIZADO: esto ayudara  a poder  crear las bases externas y evitar errores automaticos  de bloqueo de los Bootstrappers 
        \App\Tenancy\Bootstrappers\TenantDatabaseBootstrapper::class,
        
        Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class,
        Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class,
        Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,
    ],

    'database' => [
        'central_connection' => env('DB_CONNECTION', 'mysql'),
        'template_tenant_connection' => 'tenant_template', //necesario para  activar la ejecucion de dbs externas  si esta en null o no existe solo creara  en la configuracion por defecto de db en  el env
        
        'prefix' => 'gesthor_',//el nombre base  que quieres darle a tu db si lo dejas '' quedara con el mismo nombre del tennant
        'suffix' => '',

        'managers' => [
            'sqlite' => Stancl\Tenancy\TenantDatabaseManagers\SQLiteDatabaseManager::class,
            // 'mysql' => \App\Managers\HostingerDatabaseManager::class, // Manager personalizado
            'mysql' => Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager::class,
            'pgsql' => Stancl\Tenancy\TenantDatabaseManagers\PostgreSQLDatabaseManager::class,
        ],
    ],

    // agregar esto 
    'tenant_config' => [
        'database.connections.tenant.host' => 'db_node.host',
        'database.connections.tenant.port' => 'db_node.port',
        'database.connections.tenant.username' => 'db_node.username',
        'database.connections.tenant.password' => 'db_node.password',
    ],
    //debe estar asi 
     'features' => [
        // Stancl\Tenancy\Features\UserImpersonation::class,
        // Stancl\Tenancy\Features\TelescopeTags::class,
        // Stancl\Tenancy\Features\UniversalRoutes::class,
        Stancl\Tenancy\Features\TenantConfig::class, // https://tenancyforlaravel.com/docs/v3/features/tenant-config
        // Stancl\Tenancy\Features\CrossDomainRedirect::class, // https://tenancyforlaravel.com/docs/v3/features/cross-domain-redirect
        // Stancl\Tenancy\Features\ViteBundler::class,
    ],

    'migration_parameters' => [
        '--force' => true,
        '--path' => [database_path('migrations/tenant')],
        '--realpath' => true,
    ],

    'seeder_parameters' => [
        '--class' => 'Database\Seeders\TenantSeeder',//ruta personalizada para  seeder
        '--force' => true,
    ],
    //Activara  todo lo que susesde despues de crear el tennant
    'events' => [
        'tenant' => [
            'created' => [
                \App\Jobs\CreateTenantDatabase::class,
                \App\Jobs\MigrateTenantDatabase::class,
                \App\Jobs\SeedTenantDatabase::class,
            ],
            'deleted' => [
                \Stancl\Tenancy\Jobs\DeleteDatabase::class,
            ],
        ],
    ],
];
```

---

## ⚙️ Configuración de TenancyServiceProvider



## 📁 Archivos Personalizados a Crear

### 🔧 1. Bootstrapper Personalizado (⭐ CLAVE PARA CONEXIONES EXTERNAS)

**📍 Ubicación:** `app/Tenancy/Bootstrappers/TenantDatabaseBootstrapper.php`

```bash
# Crear el directorio
mkdir -p app/Tenancy/Bootstrappers
```

```php
<?php


namespace App\Tenancy\Bootstrappers;

use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantDatabaseBootstrapper implements TenancyBootstrapper
{
    public function bootstrap(Tenant $tenant)
    {
        Log::info("🔧 Iniciando bootstrap para tenant: {$tenant->id}");

        // Cargar el nodo si existe
        if ($tenant->db_node_id) {
            $tenant->load('db_node');
            $node = $tenant->db_node;

            if ($node) {
                Log::info("🌐 Tenant {$tenant->id} usa nodo externo: {$node->name} ({$node->host})");

                // Configurar la conexión 'tenant' con las credenciales del nodo
                Config::set('database.connections.tenant', [
                    'driver' => 'mysql',
                    'host' => $node->host,
                    'port' => $node->port,
                    'database' => $tenant->tenancy_db_name,
                    'username' => $node->username,
                    'password' => $node->password,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'strict' => true,
                    'engine' => null,
                ]);

                Log::info("✅ Conexión configurada - Host: {$node->host}, DB: {$tenant->tenancy_db_name}");
            }
        } else {
            // Tenant local - usar configuración por defecto de tenant_template
            $template = config('database.connections.tenant_template');
            
            Config::set('database.connections.tenant', array_merge($template, [
                'database' => $tenant->tenancy_db_name,
            ]));

            Log::info("📍 Tenant {$tenant->id} usa servidor local, DB: {$tenant->tenancy_db_name}");
        }

        // Purgar la conexión para forzar reconexión
        DB::purge('tenant');
        
        // Establecer 'tenant' como conexión por defecto
        Config::set('database.default', 'tenant');
        DB::setDefaultConnection('tenant');

        Log::info("✅ Bootstrap completado para tenant: {$tenant->id}");
    }

    public function revert()
    {
        Log::info("🔄 Revirtiendo conexión a central");
        
        // Restaurar conexión central
        $centralConnection = config('tenancy.database.central_connection');
        Config::set('database.default', $centralConnection);
        DB::setDefaultConnection($centralConnection);
        DB::purge('tenant');
    }
}
```

**💡 ¿Cómo funciona este Bootstrapper?**

1. **Se ejecuta automáticamente** cuando un tenant es inicializado
2. **Lee el `db_node_id`** del tenant actual
3. **Si existe un nodo externo:**
   - Carga las credenciales del nodo (host, puerto, usuario, contraseña)
   - **Inyecta dinámicamente** esas credenciales en `Config::set('database.connections.tenant')`
   - Laravel ahora usará esa conexión remota para todas las operaciones del tenant
4. **Si NO hay nodo:** Usa la configuración local por defecto

---


---

### 🏗️ 3. Jobs Personalizados

#### **a) CreateTenantDatabase**

**📍 Ubicación:** `app/Jobs/CreateTenantDatabase.php`

```bash
php artisan make:job CreateTenantDatabase
```

```php
<?php

namespace App\Jobs;

use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Database\DatabaseManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CreateTenantDatabase extends CreateDatabase
{
    public function handle(DatabaseManager $databaseManager)
    {
        Log::info("🏗️ Iniciando creación de DB para tenant: {$this->tenant->id}");

        // Cargar el nodo si existe
        if ($this->tenant->db_node_id) {
            $this->tenant->load('db_node');
            $node = $this->tenant->db_node;

            if (!$node) {
                throw new \Exception("Nodo {$this->tenant->db_node_id} no encontrado");
            }

            Log::info("🌐 Usando nodo: {$node->name} ({$node->host})");

            // Configurar temporalmente la conexión para el DatabaseManager
            Config::set('database.connections.tenant_template', [
                'driver' => 'mysql',
                'host' => $node->host,
                'port' => $node->port,
                'database' => 'mysql', // DB temporal para conectar
                'username' => $node->username,
                'password' => $node->password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
            ]);

            DB::purge('tenant_template');

            // Si es DB existente, NO crear
            if ($this->tenant->is_existing_db) {
                Log::info("⚠️ DB existente detectada, saltando creación: {$this->tenant->tenancy_db_name}");
                
                // Solo configurar las credenciales internas
                $this->tenant->setInternal('db_name', $this->tenant->tenancy_db_name);
                $this->tenant->save();
                
                return true;
            }
        }

        // Proceder con creación normal
        Log::info("✅ Ejecutando creación de DB estándar");
        return parent::handle($databaseManager);
    }
}
```

#### **b) MigrateTenantDatabase**

```bash
php artisan make:job MigrateTenantDatabase
```

```php
<?php

namespace App\Jobs;

use Stancl\Tenancy\Jobs\MigrateDatabase;
use Illuminate\Support\Facades\Log;

class MigrateTenantDatabase extends MigrateDatabase
{
    public function handle()
    {
        $shouldRunMigrations = $this->tenant->getInternal('run_migrations') ?? true;

        if (!$shouldRunMigrations) {
            Log::info("⏭️ Saltando Migraciones para tenant {$this->tenant->id}");
            return true;
        }

        Log::info("🔄 Ejecutando Migraciones para tenant {$this->tenant->id}");
        return parent::handle();
    }
}
```

#### **c) SeedTenantDatabase**

```bash
php artisan make:job SeedTenantDatabase
```

```php
<?php

namespace App\Jobs;

use Stancl\Tenancy\Jobs\SeedDatabase;
use Illuminate\Support\Facades\Log;

class SeedTenantDatabase extends SeedDatabase
{
    public function handle()
    {
        $shouldRunSeeders = $this->tenant->getInternal('run_seeders') ?? true;

        if (!$shouldRunSeeders) {
            Log::info("⏭️ Saltando Seeders para tenant {$this->tenant->id}");
            return true;
        }

        Log::info("🌱 Ejecutando Seeders para tenant {$this->tenant->id}");
        return parent::handle();
    }
}
```

---

### 🗃️ 4. Modelos

#### **a) Modelo Tenant**

php artisan make:model Tenant  //si o existe el modelo
**📍 Ubicación:** `app/Models/Tenant.php`

```php
<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id', 
        'tenancy_db_name', 
        'db_node_id', 
        'is_existing_db', 
        'user_limit',
    ];

    protected $casts = [
        'is_existing_db' => 'boolean',
        'user_limit' => 'integer',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'db_node_id',
            'is_existing_db',
            'user_limit',
        ];
    }

    public function db_node()
    {
        return $this->belongsTo(DbNode::class, 'db_node_id');
    }

    public function hasReachedUserLimit(): bool
    {
        return $this->run(function () {
            $userCount = \App\Models\User::count();
            return $userCount >= $this->user_limit;
        });
    }

}
```

#### **b) Modelo DbNode**

php artisan make:model DbNode 

**📍 Ubicación:** `app/Models/DbNode.php`

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DbNode extends Model
{
    protected $table = 'db_nodes';
    protected $fillable = ['name', 'host', 'username', 'password', 'port', 'is_active'];

    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'db_node_id');
    }
}
```

---

### 🌱 5. Seeder de Tenants

**📍 Ubicación:** `database/seeders/TenantSeeder.php` 

```php
    php artisan make:seeder TenantSeeder
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = tenancy()->tenant;
        $adminPassword = $tenant->getInternal('admin_password') ?? 'password';
        $tenantId = $tenant->getTenantKey();
        $userLimit = $tenant->user_limit ?? 10;

        Log::info("🌱 Iniciando seeding para tenant: {$tenantId}");

        $admin = User::create([
            'name' => 'Administrador',
            'email' => "admin@{$tenantId}.com",
            'password' => Hash::make($adminPassword),
        ]);

        Log::info("✅ Usuario admin creado: {$admin->email}");
        Log::info("🔑 Password: {$adminPassword}");
    //si quieres agregar productos  o otros datos  este es un ejemplo
        // if (DB::getSchemaBuilder()->hasTable('products')) {
        //     DB::table('products')->insert([
        //         ['name' => 'Producto Demo A', 'price' => 100, 'stock' => 50, 'created_at' => now(), 'updated_at' => now()],
        //         ['name' => 'Producto Demo B', 'price' => 200, 'stock' => 30, 'created_at' => now(), 'updated_at' => now()],
        //     ]);
        //     Log::info("✅ Productos creados");
        // }
    }
}
```
En este sider podriamos hacer el  llamdo de  otros seeders si es neceario
estos mismo solo se ejecuntan en lso tennant
---

### 💻 6. Componente Livewire

#### **Instalar livewire:**

composer require livewire/livewire

#### **Crear el componente:**

```bash
php artisan make:livewire TenantManager
```

#### **Clase:** `app/Livewire/TenantManager.php`

Puedes copiar el codigo del componente y su brade en las siguientes rutas 
app\Livewire\TenantManager.php
resources\views\livewire\tenant-manager.blade.php

ver a detalle que hace el componete 


para  probar el componente  debes copiar este archivo y crear las carpetas si no existe  resources\views\components\layouts\app.blade.php
### 💻 6. Configuracion rutas
tener en cuenta que las rutas  siempre le dara prioridad a las rutas tennat entonces si esta repetida la ruta siempre ira primero  a la del tenant
[text](https://tenancyforlaravel.com/docs/v3/routes)

en web.php

agregar Route::get('/tenant-manager', \App\Livewire\TenantManager::class);
---
### 7 .Configurador  Provider
en el archivo  app\Providers\TenancyServiceProvider.php

agregar use App\Jobs\CreateTenantDatabase; 

y en esta parte 
```php
    Events\TenantCreated::class => [
                JobPipeline::make([
                    // Jobs\CreateDatabase::class,
                    CreateTenantDatabase::class, // <--- Usa el tuyo
                    Jobs\MigrateDatabase::class,
                    Jobs\SeedDatabase::class, //para poder ejecutar los seeders personalizados

                    // Your own jobs to prepare the tenant.
                    // Provision API keys, create S3 buckets, anything you want!

                ])->send(function (Events\TenantCreated $event) {
                    return $event->tenant;
                })->shouldBeQueued(false), // `false` by default, but you probably want to make this `true` for production.
            ],
```
## 🔍 Diagrama de Flujo: Cómo Funciona la Conexión Externa

### 7 .Configurador  Livewire 
[text](https://tenancyforlaravel.com/docs/v3/integrations/livewire/#livewire)
no olvides el llamado  
use Livewire\Livewire;

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Usuario crea un Tenant desde TenantManager              │
│    - Selecciona un Nodo (servidor remoto)                  │
│    - Define si es DB existente o nueva                     │
└─────────────────────────┬───────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. Se guarda el Tenant en la DB central                    │
│    - tenant_id: "cliente1"                                 │
│    - db_node_id: 5 (referencia al servidor externo)       │
│    - tenancy_db_name: "gesthor_cliente1"                   │
└─────────────────────────┬───────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. Se dispara el evento "tenant.created"                   │
│    - CreateTenantDatabase Job                              │
│    - MigrateTenantDatabase Job                             │
│    - SeedTenantDatabase Job                                │
└─────────────────────────┬───────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. CreateTenantDatabase lee el db_node_id                  │
│    - Carga las credenciales del nodo externo               │
│    - Configura tenant_template con esas credenciales       │
│    - HostingerDatabaseManager decide si crear o no la DB   │
└─────────────────────────┬───────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│ 5. Usuario accede: http://cliente1.lvh.me:8000             │
│    - Middleware InitializeTenancyByDomain detecta el tenant│
└─────────────────────────┬───────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│ 6. TenantDatabaseBootstrapper se ejecuta ⭐ CLAVE          │
│    - Lee tenant->db_node_id                                │
│    - Carga las credenciales del nodo                       │
│    - Ejecuta: Config::set('database.connections.tenant')  │
│       • host: "servidor-remoto.com"                        │
│       • username: "usuario_remoto"                         │
│       • password: "clave_remota"                           │
│       • database: "gesthor_cliente1"                       │
│    - Laravel ahora está conectado al servidor remoto       │
└─────────────────────────┬───────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│ 7. Todas las queries usan la conexión remota               │
│    - DB::table('users')->get()                             │
│      → Se ejecuta en servidor-remoto.com                   │
│    - User::create([...])                                   │
│      → Se guarda en servidor-remoto.com                    │
└─────────────────────────────────────────────────────────────┘
```

---

## 🧪 Pruebas

### 1. Limpiar caché

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 2. Verificar que los archivos personalizados existen

```bash
# Verificar Bootstrapper
ls -la app/Tenancy/Bootstrappers/TenantDatabaseBootstrapper.php

# Verificar Manager
ls -la app/Managers/HostingerDatabaseManager.php

# Verificar Jobs
ls -la app/Jobs/CreateTenantDatabase.php
ls -la app/Jobs/MigrateTenantDatabase.php
ls -la app/Jobs/SeedTenantDatabase.php
```

### 3. Crear un tenant de prueba

```bash
php artisan tinker
```

```php
// Crear un nodo de prueba
$node = App\Models\DbNode::create([
    'name' => 'Servidor Remoto',
    'host' => 'tu-servidor-remoto.com',
    'username' => 'usuario_db',
    'password' => 'password123',
    'port' => 3306
]);
```

### 6. Instalación de Breeze para  probar sessiones auth

```bash
    composer require laravel/breeze --dev

        # Instalamos Breeze con Blade (o Livewire si prefieres)
        php artisan breeze:install blade
```
2. Mover las Rutas al Entorno de Tenants
Por defecto, Breeze pone las rutas en routes/auth.php y las carga en web.php. Para multitenancy, necesitamos que esas rutas solo existan dentro de un dominio de cliente.

Abre routes/tenant.php.

Asegúrate de que las rutas de auth estén dentro del middleware tenant:

```php

    // routes/tenant.php

    use Illuminate\Support\Facades\Route;
    use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
    use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

    Route::middleware([
        'web',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
    ])->group(function () {
        
        Route::get('/', function () {
            return 'Bienvenido al Tenant: ' . tenant('id');
        });

        // Cargar las rutas de Breeze aquí dentro
        require __DIR__.'/auth.php'; 

        Route::get('/dashboard', function () {
            return view('dashboard');
        })->middleware(['auth', 'verified'])->name('dashboard');
    });

```

en el archivo 
.env
agregar esta variable 
SESSION_DOMAIN=".lvh.me"

ejecuta el  comado 
```bash
php artisan migrate
//solo ejecuta migraciones en lso tenants
php artisan tenants:migrate
```

### 5. Crear el Observer para el Límite de Usuarios
Este componente es el "policía" que vigila que no se exceda el user_limit guardado en la tabla de la base de datos central.
```bash
    php artisan make:observer UserObserver --model=User
```
```php
    public function creating(User $user): void
    {
        // Solo validar si estamos en contexto de tenant
        if (!function_exists('tenancy') || !tenancy()->initialized) {
            return;
        }

        // Obtener el tenant actual
        $tenant = tenancy()->tenant;
        
        // Obtener el límite configurado
        $limit = $tenant->user_limit ?? 10;
        
        // Contar cuántos usuarios existen ya
        $currentCount = User::count();

        Log::info("🔍 Validando límite de usuarios", [
            'tenant' => $tenant->id,
            'limite' => $limit,
            'actuales' => $currentCount,
        ]);

        // Si ya llegó o pasó el límite, bloqueamos la creación
        if ($currentCount >= $limit) {
            Log::warning("❌ Límite de usuarios alcanzado", [
                'tenant' => $tenant->id,
                'limite' => $limit,
            ]);

            // Lanzar excepción con mensaje amigable
            throw new \Illuminate\Validation\ValidationException(
                validator: \Illuminate\Support\Facades\Validator::make([], []),
                response: null,
                errorBag: 'default'
            );
        }

        Log::info("✅ Límite de usuarios OK - Procediendo a crear usuario");
    }

    otras funciones que puedes agregar 

    public function created(User $user): void
    {
        if (function_exists('tenancy') && tenancy()->initialized) {
            $tenant = tenancy()->tenant;
            $remaining = $tenant->user_limit - User::count();
            
            Log::info("✅ Usuario creado exitosamente", [
                'tenant' => $tenant->id,
                'email' => $user->email,
                'usuarios_totales' => User::count(),
                'usuarios_restantes' => $remaining,
            ]);
        }
    }

    public function deleted(User $user): void
    {
        if (function_exists('tenancy') && tenancy()->initialized) {
            $tenant = tenancy()->tenant;
            Log::info("🗑️ Usuario eliminado", [
                'tenant' => $tenant->id,
                'email' => $user->email,
            ]);
        }
    }


```
    no olvidar hacer los llmados  de 
```php
    use App\Models\User;
    use Exception; // ← AGREGAR ESTA LÍNEA
    use Illuminate\Support\Facades\Log; // ← AGREGAR ESTA LÍNEA TAMBIÉN
```
en el  modelo de  user

agregar esto 
```php
   public function bootEvents()
    {
        // ... otros eventos ...

        $this->events()->listen(BootstrapTenancy::class, function (BootstrapTenancy $event) {
            // AQUÍ activamos el observer solo cuando un tenant ha cargado
            User::observe(UserObserver::class);
        });
        
        // ...
    }

```
    en el modelo  tenant 
    agregar esto 

    ```php
      public function hasReachedUserLimit(): bool
        {
            return $this->run(function () {
                $userCount = \App\Models\User::count();
                return $userCount >= $this->user_limit;
            });
        }

        public function getRemainingUsersAttribute(): int
        {
            return $this->run(function () {
                $userCount = \App\Models\User::count();
                return max(0, $this->user_limit - $userCount);
            });
        }
    ```
    debes agregar  en  AppServiceProvider

    esto 

    ```php
        public function boot(): void
        {
            //
            User::observe(UserObserver::class);
        }
    ```
    