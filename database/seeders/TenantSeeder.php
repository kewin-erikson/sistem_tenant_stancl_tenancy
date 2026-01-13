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

        // if (DB::getSchemaBuilder()->hasTable('products')) {
        //     DB::table('products')->insert([
        //         ['name' => 'Producto Demo A', 'price' => 100, 'stock' => 50, 'created_at' => now(), 'updated_at' => now()],
        //         ['name' => 'Producto Demo B', 'price' => 200, 'stock' => 30, 'created_at' => now(), 'updated_at' => now()],
        //     ]);
        //     Log::info("✅ Productos creados");
        // }
    }
}