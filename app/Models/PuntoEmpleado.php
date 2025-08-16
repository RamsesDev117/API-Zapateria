<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PuntoEmpleado extends Model
{
    protected $table = 'puntos_empleado';

    protected $fillable = [
        'usuario_id',
        'puntos'
    ];

}
