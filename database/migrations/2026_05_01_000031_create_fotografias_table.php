<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fotografias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesaje_id')
                ->constrained('pesajes')
                ->onDelete('cascade');
            $table->string('ruta_archivo')
                ->comment('Ruta o URL en DigitalOcean Spaces');
            $table->dateTime('fecha_captura');
            $table->string('resolucion', 20)->nullable()
                ->comment('Ej: 1920x1080');
            $table->boolean('es_valida')->default(false)
                ->comment('Si paso la validacion de nitidez y deteccion de bovino');
            $table->string('motivo_invalidez')->nullable();
            $table->timestamps();

            $table->index('pesaje_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fotografias');
    }
};
