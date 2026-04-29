<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fincas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('propietario_id')
                ->constrained('users')
                ->onDelete('restrict')
                ->comment('Usuario con rol propietario que es dueno de la finca');
            $table->string('nombre');
            $table->string('provincia');
            $table->string('canton');
            $table->string('distrito');
            $table->date('fecha_creacion')->useCurrent();
            $table->timestamps();

            $table->index('propietario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fincas');
    }
};
