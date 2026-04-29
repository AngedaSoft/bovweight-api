<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('finca_id')
                ->nullable()
                ->constrained('fincas')
                ->nullOnDelete();
            $table->enum('tipo', ['pdf', 'excel']);
            $table->dateTime('fecha_generacion')->useCurrent();
            $table->string('ruta_archivo')
                ->comment('Ruta o URL en DigitalOcean Spaces');
            $table->json('parametros')->nullable()
                ->comment('Filtros aplicados al generar el reporte');
            $table->timestamps();

            $table->index(['usuario_id', 'fecha_generacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
