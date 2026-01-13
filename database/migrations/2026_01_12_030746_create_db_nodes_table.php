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