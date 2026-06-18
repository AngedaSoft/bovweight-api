<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pesajes', function (Blueprint $table) {
            $table->text('formula_zootecnica')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pesajes', function (Blueprint $table) {
            $table->string('formula_zootecnica', 50)->nullable()->change();
        });
    }
};
