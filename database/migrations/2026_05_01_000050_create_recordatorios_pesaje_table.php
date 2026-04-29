<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recordatorios_pesaje', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')
                ->constrained('animales')
                ->onDelete('cascade');
            $table->foreignId('usuario_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->integer('frecuencia_dias');
            $table->date('proxima_fecha');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['usuario_id', 'activo', 'proxima_fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordatorios_pesaje');
    }
};
