<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Apartado extends Model
{
    protected $table = 'apartados';

    protected $fillable = [
        'zapato_id',
        'nombre_cliente',
        'telefono_cliente',
        'fecha_apartado',
        'fecha_limite',
        'monto_apartado',
        'monto_restante',
        'monto_pagado',
        'precio_zapato',
        'estado',
        'usuario_id',
        'apartado_sucursal_id',
        'zapato_sucursal_id',
    ];

    public function zapato(): BelongsTo
    {
        return $this->belongsTo(Zapato::class, 'zapato_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function sucursalApartado(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'apartado_sucursal_id');
    }

    public function sucursalZapato(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'zapato_sucursal_id');
    }
}
