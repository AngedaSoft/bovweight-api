<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rebanos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finca_id')
                ->constrained('fincas')
                ->onDelete('cascade');
            $table->string('nombre');
            $table->enum('proposito', ['terneros', 'vacas_ordeno', 'engorde', 'cria', 'otro'])->default('otro');
            $table->date('fecha_creacion')->useCurrent();
            $table->timestamps();

            $table->index(['finca_id', 'proposito']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rebanos');
    }
};
