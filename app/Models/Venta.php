<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'folio',
        'fecha',
        'total',
        'usuario_id',
        'venta_sucursal_id',
        'zapato_sucursal_id',
        'metodo_pago',
        'corte_caja_id'
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function sucursalVenta(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'venta_sucursal_id');
    }

    public function sucursalZapato(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'zapato_sucursal_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function corteCaja(): BelongsTo
    {
        return $this->belongsTo(CorteCaja::class, 'corte_caja_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'venta_sucursal_id');
    }

}
