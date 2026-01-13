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