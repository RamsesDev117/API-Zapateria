<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles a insertar
        $roles = [
            [
                'nombre' => Rol::SUPER_ADMINISTRADOR,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => Rol::ADMINISTRADOR,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => Rol::EMPLEADO_TIENDA,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => Rol::EMPLEADO_BODEGA,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Insertar roles en la base de datos
        Rol::insert($roles);

        $this->command->info('Roles creados exitosamente!');
    }
}
