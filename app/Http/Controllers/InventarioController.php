<?php

namespace App\Http\Controllers;

use App\Models\PuntoEmpleado;
use App\Models\Sucursal;
use App\Models\Usuario;
use App\Models\ZapatoSucursal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Zapato;
use Illuminate\Validation\ValidationException;

class InventarioController extends Controller
{
    //Metodo para crear un zapato
    public function storeZapatos(Request $request)
    {
        // Aquí asumimos que el payload es un array de objetos
        $data = $request->all();

        // Empaquetamos en "zapatos" para usar la validación con *.campo
        $validator = Validator::make(['zapatos' => $data], [
            'zapatos' => 'required|array',
            'zapatos.*.codigo' => 'required|string|max:225|unique:zapatos,codigo',
            'zapatos.*.tipo' => 'required|string|max:225',
            'zapatos.*.marca' => 'required|string|max:225',
            'zapatos.*.modelo' => 'required|string|max:225',
            'zapatos.*.material' => 'required|string|max:225',
            'zapatos.*.color' => 'required|string|max:225',
            'zapatos.*.talla' => 'required|string|max:225',
            'zapatos.*.precio' => 'required|numeric|min:0',
            'zapatos.*.imagen' => 'nullable|string',
            'zapatos.*.estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Guardar cada zapato
        $zapatosGuardados = [];
        foreach ($validator->validated()['zapatos'] as $zapatoData) {
            // Como tus campos son tipo, marca, modelo, etc.
            // el modelo Zapato espera tipo_zapato, así que mapea aquí
            $zapatosGuardados[] = Zapato::create([
                'codigo' => $zapatoData['codigo'],
                'tipo_zapato' => $zapatoData['tipo'],
                'marca' => $zapatoData['marca'],
                'modelo' => $zapatoData['modelo'],
                'material' => $zapatoData['material'],
                'color' => $zapatoData['color'],
                'talla' => $zapatoData['talla'],
                'precio' => $zapatoData['precio'],
                'imagen' => $zapatoData['imagen'] ?? null,
                'estado' => $zapatoData['estado'],
            ]);
        }

        return response()->json([
            'message' => 'Zapatos registrados correctamente',
            'zapatos' => $zapatosGuardados
        ], 201);
    }

    //Metodo para listar zapatos por indice compuesto
    public function indexInventario(Request $request)
    {
        $query = Zapato::query();

        // Filtrado por campos del índice compuesto
        if ($request->filled('marca')) {
            $query->where('marca', $request->marca);
        }

        if ($request->filled('modelo')) {
            $query->where('modelo', $request->modelo);
        }

        if ($request->filled('material')) {
            $query->where('material', $request->material);
        }

        if ($request->filled('color')) {
            $query->where('color', $request->color);
        }

        if ($request->filled('talla')) {
            $query->where('talla', $request->talla);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $zapatos = $query->get();

        return response()->json([
            'data' => $zapatos
        ]);
    }

    //Metodo para registrar inventarios de zapatos en sucursales
    public function storeInventario(Request $request)
    {
        $data = $request->all();

        if (!is_array($data)) {
            return response()->json([
                'message' => 'La petición debe ser un array de inventarios.'
            ], 422);
        }

        DB::beginTransaction(); // Inicia la transacción

        try {
            foreach ($data as $index => $item) {
                // Validación de cada ítem
                $validator = Validator::make($item, [
                    'zapato_id' => 'required|exists:zapatos,id',
                    'sucursal_id' => 'required|exists:sucursales,id',
                    'unidades_disponibles' => 'required|integer|min:0',
                ]);

                if ($validator->fails()) {
                    throw ValidationException::withMessages([
                        "item_{$index}" => $validator->errors()->all()
                    ]);
                }

                // Verificar existencia previa
                $existe = ZapatoSucursal::where('zapato_id', $item['zapato_id'])
                    ->where('sucursal_id', $item['sucursal_id'])
                    ->exists();

                if ($existe) {
                    throw ValidationException::withMessages([
                        "item_{$index}" => ['Ya existe un inventario registrado para este zapato en la sucursal.']
                    ]);
                }

                // Crear inventario
                ZapatoSucursal::create($item);
            }

            DB::commit(); //

            return response()->json([
                'message' => 'Todos los inventarios fueron registrados correctamente.',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack(); // Algo falló, revertir todo

            if ($e instanceof ValidationException) {
                throw $e;
            }

            // Si es otro tipo de error
            return response()->json([
                'message' => 'Error al registrar inventarios.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //Metodo para listar zapatos por sucursal
    public function listarPorSucursal(Request $request)
    {
        // Validar sucursal_id obligatorio
        $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id'
        ]);

        // Obtener filtros opcionales
        $marca = $request->input('marca');
        $modelo = $request->input('modelo');
        $material = $request->input('material');
        $color = $request->input('color');
        $talla = $request->input('talla');

        // Construir query
        $query = ZapatoSucursal::with('zapato')
            ->where('sucursal_id', $request->sucursal_id);

        // Filtrar por atributos del zapato si se proporcionan
        $query->whereHas('zapato', function ($q) use ($marca, $modelo, $material, $color, $talla) {
            if ($marca) {
                $q->where('marca', $marca);
            }
            if ($modelo) {
                $q->where('modelo', $modelo);
            }
            if ($material) {
                $q->where('material', $material);
            }
            if ($color) {
                $q->where('color', $color);
            }
            if ($talla) {
                $q->where('talla', $talla);
            }
        });

        $inventario = $query->get();

        return response()->json([
            'data' => $inventario
        ]);
    }

    //Metodo para trasladar zapatos entre sucursales
    public function trasladarZapato(Request $request)
    {
        $validated = $request->validate([
            'zapato_id' => 'required|exists:zapatos,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'cantidad' => 'required|integer|min:1'
        ]);

        // Sucursal destino (TIENDA)
        $sucursalDestino = Sucursal::findOrFail($validated['sucursal_id']);

        if ($sucursalDestino->tipo !== Sucursal::TIPO_TIENDA) {
            throw ValidationException::withMessages([
                'sucursal_id' => ['La sucursal destino no es de tipo TIENDA.']
            ]);
        }

        // Buscar una sucursal de tipo BODEGA con stock del zapato
        $bodega = Sucursal::where('tipo', Sucursal::TIPO_BODEGA)
            ->whereHas('inventarios', function ($query) use ($validated) {
                $query->where('zapato_id', $validated['zapato_id'])
                    ->where('unidades_disponibles', '>=', $validated['cantidad']);
            })->first();

        if (!$bodega) {
            throw ValidationException::withMessages([
                'bodega' => ['No hay stock disponible en ninguna sucursal de tipo BODEGA.']
            ]);
        }

        DB::beginTransaction();

        try {
            // Descontar unidades de la bodega
            $inventarioBodega = ZapatoSucursal::where('zapato_id', $validated['zapato_id'])
                ->where('sucursal_id', $bodega->id)
                ->first();

            $inventarioBodega->decrement('unidades_disponibles', $validated['cantidad']);

            // Agregar o actualizar inventario en la sucursal destino
            $inventarioDestino = ZapatoSucursal::firstOrNew([
                'zapato_id' => $validated['zapato_id'],
                'sucursal_id' => $validated['sucursal_id'],
            ]);

            $inventarioDestino->unidades_disponibles = ($inventarioDestino->unidades_disponibles ?? 0) + $validated['cantidad'];
            $inventarioDestino->save();

            DB::commit();

            return response()->json([
                'message' => "Traslado realizado correctamente desde la bodega '{$bodega->nombre}'",
                'origen_bodega' => $bodega->nombre,
                'destino' => $sucursalDestino->nombre,
                'cantidad_trasladada' => $validated['cantidad'],
                'inventario_actual_destino' => $inventarioDestino->unidades_disponibles
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al realizar el traslado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //Metodo para listar todos los zapatos
    public function indexZapatos()
    {
        $zapatos = Zapato::all();

        return response()->json([
            'message' => 'Lista de zapatos obtenida correctamente',
            'zapatos' => $zapatos
        ], 200);
    }

    //Metodo para obtener un zapato por ID
    public function showZapato($id)
    {
        $zapato = Zapato::find($id);

        if (!$zapato) {
            return response()->json([
                'message' => 'Zapato no encontrado'
            ], 404);
        }

        return response()->json([
            'message' => 'Zapato encontrado',
            'zapato' => $zapato
        ], 200);
    }

    //Metodo para filtrar puntos de empleados
    public function filtrarPuntos(Request $request)
    {
        $validated = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'usuario_id' => 'nullable|exists:usuarios,id',
        ]);

        $query = PuntoEmpleado::query()
            ->join('usuarios', 'puntos_empleado.usuario_id', '=', 'usuarios.id')
            ->whereDate('puntos_empleado.created_at', '>=', $validated['fecha_inicio'])
            ->whereDate('puntos_empleado.created_at', '<=', $validated['fecha_fin']);

        if (!empty($validated['usuario_id'])) {
            $query->where('puntos_empleado.usuario_id', $validated['usuario_id']);
        }

        $resultados = $query->select(
            'usuarios.id as usuario_id',
            'usuarios.nombre',
            'puntos_empleado.puntos',
            DB::raw('DATE(puntos_empleado.created_at) as fecha')
        )->orderBy('fecha')->get();

        return response()->json([
            'data' => $resultados
        ]);
    }

    // Metodo para actualizar unidades disponibles de un zapato en una sucursal
    public function actualizarUnidades(Request $request, $id)
    {
        // Validación de datos
        $validated = $request->validate([
            'unidades_disponibles' => 'required|integer|min:0'
        ]);

        // Buscar el registro de inventario
        $inventario = ZapatoSucursal::findOrFail($id);

        // Actualizar el valor (sobrescribir, no sumar)
        $inventario->unidades_disponibles = $validated['unidades_disponibles'];
        $inventario->save();

        return response()->json([
            'message' => 'Inventario actualizado correctamente',
            'data' => $inventario
        ]);
    }

    //Metodo para obtener empleados de bodega
    public function getEmpBodega()
    {
        $usuarios = Usuario::where('sucursal_id', 1)->get();

        return response()->json($usuarios);
    }

}
