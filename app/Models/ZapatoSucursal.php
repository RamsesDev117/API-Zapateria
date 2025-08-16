<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZapatoSucursal extends Model
{
    protected $table = 'zapatos_sucursal';

    protected $fillable = [
        'zapato_id',
        'sucursal_id',
        'unidades_disponibles'
    ];

    public function zapato(): BelongsTo
    {
        return $this->belongsTo(Zapato::class, 'zapato_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

}
