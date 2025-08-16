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
        Schema::create('zapatos_sucursal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zapato_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->integer('unidades_disponibles')->default(0);
            $table->timestamps();

            $table->foreign('zapato_id')->references('id')->on('zapatos')->onDelete('cascade');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');

            // Índices para mejorar el rendimiento
            $table->index(['zapato_id', 'sucursal_id'], 'zapato_sucursal_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zapatos_sucursal');
    }
};
