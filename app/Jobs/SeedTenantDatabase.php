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