<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acceso_compartido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finca_id')
                ->constrained('fincas')
                ->onDelete('cascade');
            $table->foreignId('usuario_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Veterinario o usuario al que se le otorga acceso');
            $table->dateTime('fecha_inicio')->useCurrent();
            $table->dateTime('fecha_fin')->nullable();
            $table->enum('tipo_acceso', ['lectura', 'edicion'])->default('lectura');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['finca_id', 'usuario_id'], 'uq_acceso_finca_usuario');
            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acceso_compartido');
    }
};
