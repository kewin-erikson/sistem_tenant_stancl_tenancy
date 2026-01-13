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