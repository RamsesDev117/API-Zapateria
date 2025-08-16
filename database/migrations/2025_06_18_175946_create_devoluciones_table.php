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
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('venta_id');
            $table->unsignedBigInteger('zapato_id');
            $table->string('motivo');
            $table->date('fecha');
            $table->string('tipo_cambio');
            $table->unsignedBigInteger('zapato_intercambio_id');

            $table->timestamps();

            $table->foreign('venta_id')->references('id')->on('ventas')->onDelete('cascade');
            $table->foreign('zapato_id')->references('id')->on('zapatos')->onDelete('cascade');
            $table->foreign('zapato_intercambio_id')->references('id')->on('zapatos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devoluciones');
    }
};
