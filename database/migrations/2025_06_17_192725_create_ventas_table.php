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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('folio',225)->unique();
            $table->date('fecha');
            $table->double('total');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('venta_sucursal_id');
            $table->unsignedBigInteger('zapato_sucursal_id');
            $table->string('metodo_pago', 50); // Efectivo, tarjeta, etc.
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('venta_sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
            $table->foreign('zapato_sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
