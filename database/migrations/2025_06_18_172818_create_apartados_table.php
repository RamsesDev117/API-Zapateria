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
        Schema::create('apartados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zapato_id');
            $table->string('nombre_cliente');
            $table->string('telefono_cliente');
            $table->date('fecha_apartado');
            $table->date('fecha_limite')->nullable();
            $table->decimal('monto_apartado', 10, 2);
            $table->decimal('monto_restante', 10, 2)->default(0);
            $table->decimal('monto_pagado', 10, 2)->default(0);
            $table->decimal('precio_zapato', 10, 2);
            $table->enum('estado', ['ACTIVO', 'CANCELADO', 'COMPLETADO'])->default('activo');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('apartado_sucursal_id');
            $table->unsignedBigInteger('zapato_sucursal_id');
            $table->timestamps();

            $table->foreign('zapato_id')->references('id')->on('zapatos')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('apartado_sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
            $table->foreign('zapato_sucursal_id')->references('id')->on('sucursales')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartados');
    }
};
