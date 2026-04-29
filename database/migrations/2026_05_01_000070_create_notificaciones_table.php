<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->string('tipo', 50)
                ->comment('Ej: recordatorio_pesaje, transferencia_solicitada, reporte_listo');
            $table->string('mensaje');
            $table->json('payload')->nullable()
                ->comment('Datos contextuales de la notificacion');
            $table->dateTime('fecha_programada')->nullable();
            $table->dateTime('fecha_envio')->nullable();
            $table->boolean('leida')->default(false);
            $table->timestamps();

            $table->index(['usuario_id', 'leida']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
