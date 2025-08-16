<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zapato extends Model
{
    protected $table = 'zapatos';

    protected $fillable = [
        'codigo',
        'tipo_zapato',
        'marca',
        'modelo',
        'material',
        'color',
        'talla',
        'precio',
        'imagen',
        'estado'
    ];

    // Un zapato puede estar en muchas sucursales (relación uno a muchos con ZapatoSucursal)
    public function inventarios(): HasMany
    {
        return $this->hasMany(ZapatoSucursal::class, 'zapato_id');
    }

    public function apartados(): HasMany
    {
        return $this->hasMany(Apartado::class, 'zapato_id');
    }
}
