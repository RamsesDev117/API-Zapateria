<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Usuario::firstOrCreate(
            ['usuario' => 'SuperAdmin'], // Campos únicos para buscar
            [ // Campos a insertar si no existe
                'nombre' => 'Super Administrador',
                'password' => Hash::make('admin1234'),
                'rol_id' => 1,
                'sucursal_id' => 4,
                'estado' => 'ACTIVO',
            ]
        );

        $this->command->info('Usario creado exitosamente!');
    }
}
