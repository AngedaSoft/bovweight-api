<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correcciones_peso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesaje_id')
                ->constrained('pesajes')
                ->onDelete('cascade');
            $table->foreignId('usuario_id')
                ->constrained('users')
                ->onDelete('restrict');
            $table->decimal('peso_original_kg', 8, 2);
            $table->decimal('peso_corregido_kg', 8, 2);
            $table->string('motivo', 500)->nullable();
            $table->dateTime('fecha')->useCurrent();
            $table->timestamps();

            $table->index('pesaje_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correcciones_peso');
    }
};
