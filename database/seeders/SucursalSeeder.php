<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class SucursalSeeder extends Seeder
{
    public function run(): void
    {
        // Sucursales a insertar
        $sucursales = [
            [
                'nombre' => 'Bodega Central',
                'tipo' => Sucursal::TIPO_BODEGA,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Tienda 1',
                'tipo' => Sucursal::TIPO_TIENDA,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Tienda 2',
                'tipo' => Sucursal::TIPO_TIENDA,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Oficina Administrativa',
                'tipo' => Sucursal::TIPO_ADMINISTRATIVA,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Insertar sucursales en la base de datos
        Sucursal::insert($sucursales);

        $this->command->info('Sucursales creadas exitosamente!');
    }
}
