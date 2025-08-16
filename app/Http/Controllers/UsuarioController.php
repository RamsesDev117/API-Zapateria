<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    // Crear un nuevo usuario
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'usuario' => 'required|string|max:50|unique:usuarios,usuario',
            'password' => 'required|string|min:6',
            'rol_id' => 'required|exists:roles,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $usuario = Usuario::create([
            'nombre' => $validated['nombre'],
            'usuario' => $validated['usuario'],
            'password' => Hash::make($validated['password']),
            'rol_id' => $validated['rol_id'],
            'sucursal_id' => $validated['sucursal_id'],
            'estado' => $validated['estado'],
        ]);

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'usuario' => $usuario
        ], 201);
    }

    // Actualizar un usuario existente
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
            'usuario' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('usuarios', 'usuario')->ignore($usuario->id)],
            'password' => 'nullable|string|min:6',
            'rol_id' => 'sometimes|required|exists:roles,id',
            'sucursal_id' => 'sometimes|required|exists:sucursales,id',
            'estado' => ['sometimes', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        // Si incluye password, encriptar
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $usuario->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'usuario' => $usuario
        ]);
    }

    // Desactivar un usuario
    public function deactivate($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->estado = 'INACTIVO';
        $usuario->save();

        return response()->json([
            'message' => 'Usuario desactivado correctamente',
            'usuario' => $usuario
        ]);
    }

    // Activar un usuario
    public function activate($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->estado = 'ACTIVO';
        $usuario->save();

        return response()->json([
            'message' => 'Usuario activado correctamente',
            'usuario' => $usuario
        ]);
    }

    // Listar usuarios con filtros opcionales
    public function index(Request $request)
    {
        $query = Usuario::with(['rol', 'sucursal']); // Cargar relaciones

        if ($request->has('rol_id')) {
            $query->where('rol_id', $request->rol_id);
        }

        if ($request->has('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', strtoupper($request->estado));
        }

        $usuarios = $query->get();

        // Si quieres que la respuesta solo devuelva los datos relevantes
        $usuarios = $usuarios->map(function ($usuario) {
            return [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'usuario' => $usuario->usuario,
                'rol' => $usuario->rol->nombre ?? null,
                'sucursal' => $usuario->sucursal->nombre ?? null,
                'estado' => $usuario->estado,
                'created_at' => $usuario->created_at,
                'updated_at' => $usuario->updated_at,
            ];
        });

        return response()->json($usuarios);
    }

    // Metodo para obtener roles
    public function getRoles()
    {
        $roles = Rol::all();
        return response()->json($roles);
    }

    // Metodo para obtener sucursales
    public function getSucursales()
    {
        $sucursales = Sucursal::all();
        return response()->json($sucursales);
    }


}
