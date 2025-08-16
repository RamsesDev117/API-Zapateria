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
        Schema::create('zapatos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo',225)->unique();
            $table->string('tipo_zapato',225);
            $table->string('marca',225);
            $table->string('modelo',225);
            $table->string('material',225);
            $table->string('color',225);
            $table->string('talla',225);
            $table->double('precio');
            $table->string('imagen')->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            // Índice compuesto
            $table->index(['marca', 'modelo', 'material', 'color', 'talla'], 'zapatos_detalle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zapatos');
    }
};
