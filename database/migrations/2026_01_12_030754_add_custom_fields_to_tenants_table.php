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