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
            } else {
            // ✅ CASO LOCAL: Configurar tenant_template con las credenciales locales
            Log::info("📍 Usando servidor LOCAL (sin nodo externo)");
            
            // Obtener la configuración de la conexión central
            $centralConnection = config('tenancy.database.central_connection', 'mysql');
            $centralConfig = config("database.connections.{$centralConnection}");
            
            // Configurar tenant_template con las credenciales locales
            Config::set('database.connections.tenant_template', [
                'driver' => $centralConfig['driver'] ?? 'mysql',
                'host' => $centralConfig['host'] ?? '127.0.0.1',
                'port' => $centralConfig['port'] ?? '3306',
                'database' => 'mysql', // DB temporal para conectar
                'username' => $centralConfig['username'] ?? 'root',
                'password' => $centralConfig['password'] ?? '',
                'charset' => $centralConfig['charset'] ?? 'utf8mb4',
                'collation' => $centralConfig['collation'] ?? 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
            ]);

            DB::purge('tenant_template');
            
            Log::info("⚙️ Configuración tenant_template establecida desde conexión central: {$centralConnection}");
        }

        // Proceder con creación normal
        Log::info("✅ Ejecutando creación de DB estándar");
        return parent::handle($databaseManager);
    }
}