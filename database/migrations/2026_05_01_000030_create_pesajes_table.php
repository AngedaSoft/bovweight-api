<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')
                ->constrained('animales')
                ->onDelete('cascade');
            $table->foreignId('usuario_id')
                ->constrained('users')
                ->onDelete('restrict')
                ->comment('Usuario que registro el pesaje');

            $table->dateTime('fecha');
            $table->decimal('peso_estimado_kg', 8, 2);
            $table->decimal('rango_confianza_kg', 6, 2)->nullable()
                ->comment('Margen +/- en kg que devuelve el servicio de IA');

            $table->boolean('fue_corregido')->default(false);
            $table->decimal('peso_corregido_kg', 8, 2)->nullable();

            // Tipo (discriminator de herencia: PesajeIA / PesajeManual)
            $table->enum('tipo', ['ia', 'manual']);

            // Campos especificos de PesajeIA
            $table->string('modelo_ia_version', 50)->nullable()
                ->comment('Version del modelo YOLOv8 que ejecuto la inferencia');
            $table->integer('tiempo_procesamiento_seg')->nullable();
            $table->enum('estado_procesamiento', ['pendiente', 'procesada', 'fallida'])->nullable();

            // Campos especificos de PesajeManual
            $table->string('formula_zootecnica', 50)->nullable()
                ->comment('Formula usada en pesaje manual: schaeffer, agarwal, regresion_local');
            $table->boolean('es_offline')->default(false);

            // Medidas morfologicas extraidas (vienen del servicio de IA o ingresadas manualmente)
            $table->decimal('perimetro_toracico_cm', 6, 2)->nullable();
            $table->decimal('largo_cuerpo_cm', 6, 2)->nullable();
            $table->date('fecha_medicion')->nullable();

            $table->timestamps();

            $table->index(['animal_id', 'fecha']);
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesajes');
    }
};
