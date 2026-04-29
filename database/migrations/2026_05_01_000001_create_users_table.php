<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo');
            $table->string('correo')->unique();
            $table->string('contrasena_hash');
            $table->enum('rol', ['propietario', 'veterinario', 'administrador'])->default('propietario');
            $table->enum('estado', ['activo', 'inactivo', 'bloqueado'])->default('activo');
            $table->date('fecha_registro')->useCurrent();
            $table->dateTime('ultimo_acceso')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('correo');
            $table->index('rol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
