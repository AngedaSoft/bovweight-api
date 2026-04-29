<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transferencias_animal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')
                ->constrained('animales')
                ->onDelete('restrict');
            $table->foreignId('vendedor_id')
                ->constrained('users')
                ->onDelete('restrict');
            $table->foreignId('comprador_id')
                ->constrained('users')
                ->onDelete('restrict');
            $table->foreignId('finca_destino_id')
                ->nullable()
                ->constrained('fincas')
                ->nullOnDelete();
            $table->dateTime('fecha_solicitud')->useCurrent();
            $table->dateTime('fecha_aceptacion')->nullable();
            $table->enum('estado', ['pendiente', 'aceptada', 'rechazada'])->default('pendiente');
            $table->string('motivo_rechazo')->nullable();
            $table->timestamps();

            $table->index(['comprador_id', 'estado']);
            $table->index(['vendedor_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transferencias_animal');
    }
};
