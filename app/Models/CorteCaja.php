<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorteCaja extends Model
{
    protected $table = 'cortes_caja';

    protected $fillable = [
        'usuario_id',
        'corte_sucursal_id',
        'turno',
        'fecha_inicio',
        'fecha_fin',
        'detalles',
        'total_ventas'
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'corte_sucursal_id'); // 👈 corregido
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'corte_caja_id');
    }
}
