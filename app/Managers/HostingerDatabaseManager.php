<?php

namespace App\Managers;

use Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HostingerDatabaseManager extends MySQLDatabaseManager
{
    /**
     * Sobrescribimos este método para EVITAR el CREATE DATABASE en Hostinger
     */
    public function createDatabase(TenantWithDatabase $tenant): bool
    {
        // Si el tenant tiene marcado que usa DB existente, NO creamos nada
        if ($tenant->is_existing_db) {
            Log::info("Saltando creación de DB para tenant {$tenant->id} - usando DB existente: {$tenant->tenancy_db_name}");
            return true;
        }

        // Si NO es DB existente pero SÍ hay nodo externo, tampoco creamos
        // porque Hostinger no lo permite (Error 1044)
        if ($tenant->db_node_id) {
            Log::warning("Saltando creación de DB para tenant {$tenant->id} en nodo externo. Asegúrate que la DB exista.");
            return true;
        }

        // Si es local sin nodo externo, usamos el comportamiento normal
        return parent::createDatabase($tenant);
    }

    /**
     * Método para verificar si la DB existe en el servidor remoto
     */
    public function databaseExists(TenantWithDatabase $tenant): bool
    {
        $database = $tenant->database();
        $name = $database->getName();

        try {
            $connection = $this->getConnection($tenant);
            $databases = DB::connection($connection)->select('SHOW DATABASES');
            
            foreach ($databases as $db) {
                if ($db->Database === $name) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error("Error verificando DB: " . $e->getMessage());
            return false;
        }
    }
}