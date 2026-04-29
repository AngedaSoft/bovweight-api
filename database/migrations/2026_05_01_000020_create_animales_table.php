<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finca_id')
                ->constrained('fincas')
                ->onDelete('cascade');
            $table->foreignId('rebano_id')
                ->nullable()
                ->constrained('rebanos')
                ->nullOnDelete();
            $table->foreignId('raza_id')
                ->nullable()
                ->constrained('razas')
                ->nullOnDelete();

            // Identificacion oficial (Ministerio de Agricultura - Costa Rica)
            $table->string('arete_senasa', 50)->unique()
                ->comment('Identificador oficial unico por animal');
            $table->date('fecha_asignacion_arete')->nullable();

            // Identificacion del animal
            $table->string('nombre')->nullable();
            $table->date('fecha_nacimiento_aprox')->nullable();
            $table->enum('sexo', ['macho', 'hembra']);

            // Estado y trazabilidad
            $table->enum('estado', [
                'activo',
                'inactivo_vendido',
                'inactivo_muerto',
                'inactivo_traslado',
                'inactivo_transferido'
            ])->default('activo');
            $table->string('motivo_inactivacion')->nullable();
            $table->date('fecha_inactivacion')->nullable();

            $table->timestamps();

            $table->index(['finca_id', 'estado']);
            $table->index('arete_senasa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animales');
    }
};
