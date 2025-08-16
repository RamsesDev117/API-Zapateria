<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = ['nombre'];

    // Constantes para los roles
    const SUPER_ADMINISTRADOR = 'Super Administrador';
    const ADMINISTRADOR = 'Administrador';
    const EMPLEADO_TIENDA = 'Empleado Tienda';
    const EMPLEADO_BODEGA = 'Empleado Bodega';

    // Relación con usuarios
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
