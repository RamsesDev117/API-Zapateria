<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'tipo',
    ];

    const TIPO_BODEGA = 'BODEGA';
    const TIPO_TIENDA = 'TIENDA';
    const TIPO_ADMINISTRATIVA = 'ADMINISTRATIVA';

    public function inventarios(): HasMany
    {
        return $this->hasMany(ZapatoSucursal::class, 'sucursal_id');
    }

    public function apartadosRegistrados(): HasMany
    {
        return $this->hasMany(Apartado::class, 'apartado_sucursal_id');
    }

    public function apartadosZapatos(): HasMany
    {
        return $this->hasMany(Apartado::class, 'zapato_sucursal_id');
    }

    public function ventasRegistradas(): HasMany
    {
        return $this->hasMany(Venta::class, 'venta_sucursal_id');
    }

    public function ventasZapatos(): HasMany
    {
        return $this->hasMany(Venta::class, 'zapato_sucursal_id');
    }

    public function cortesCaja(): HasMany
    {
        return $this->hasMany(CorteCaja::class, 'corte_sucursal_id');
    }
}
