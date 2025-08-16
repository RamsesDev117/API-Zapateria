<?php

namespace App\Http\Controllers;

use App\Models\Apartado;
use App\Models\CorteCaja;
use App\Models\DetalleVenta;
use App\Models\PuntoEmpleado;
use App\Models\Usuario;
use App\Models\Venta;
use App\Models\Zapato;
use App\Models\ZapatoSucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TiendaController extends Controller
{

    //Metodo para listar zapatos por indice compuesto
    public function getZapato(Request $request)
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

    //Metodo para obtener empleados de bodega
    public function getEmpBodega()
    {
        $usuarios = Usuario::where('sucursal_id', 1)->get();

        return response()->json($usuarios);
    }

    // Método para registrar una venta
    public function registrarVentas(Request $request)
    {
        $ventas = $request->all();

        if (!is_array($ventas) || empty($ventas)) {
            return response()->json([
                'message' => 'Debe enviar un arreglo con al menos una venta.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($ventas as $ventaData) {
                // Validar individualmente cada venta
                $validated = validator($ventaData, [
                    'folio' => 'required|unique:ventas,folio',
                    'fecha' => 'required|date',
                    'usuario_id' => 'required|exists:usuarios,id',
                    'venta_sucursal_id' => 'required|exists:sucursales,id',
                    'zapato_sucursal_id' => 'required|exists:sucursales,id',
                    'metodo_pago' => 'required|string|max:50',
                    'empleado_id' => 'required|exists:usuarios,id',
                    'detalles' => 'required|array|min:1',
                    'detalles.*.zapato_id' => 'required|exists:zapatos,id',
                    'detalles.*.cantidad' => 'required|integer|min:1',
                    'detalles.*.precio_unitario' => 'required|numeric|min:0',
                ])->validate();

                // Crear la venta
                $venta = Venta::create([
                    'folio' => $validated['folio'],
                    'fecha' => $validated['fecha'],
                    'usuario_id' => $validated['usuario_id'],
                    'venta_sucursal_id' => $validated['venta_sucursal_id'],
                    'zapato_sucursal_id' => $validated['zapato_sucursal_id'],
                    'metodo_pago' => $validated['metodo_pago'],
                    'total' => 0,
                ]);

                $totalVenta = 0;
                $puntosGanados = 0;

                foreach ($validated['detalles'] as $detalle) {
                    $inventario = ZapatoSucursal::where('zapato_id', $detalle['zapato_id'])
                        ->where('sucursal_id', $validated['zapato_sucursal_id'])
                        ->first();

                    if (!$inventario || $inventario->unidades_disponibles < $detalle['cantidad']) {
                        throw ValidationException::withMessages([
                            'inventario' => ["El zapato con ID {$detalle['zapato_id']} no está disponible o no hay suficiente stock en la sucursal seleccionada."]
                        ]);
                    }

                    DetalleVenta::create([
                        'venta_id' => $venta->id,
                        'zapato_id' => $detalle['zapato_id'],
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $detalle['precio_unitario'],
                    ]);

                    $inventario->decrement('unidades_disponibles', $detalle['cantidad']);

                    $totalVenta += $detalle['cantidad'] * $detalle['precio_unitario'];
                    $puntosGanados += $detalle['cantidad'];
                }

                // Actualizar total
                $venta->total = $totalVenta;
                $venta->save();

                // Solo asignar puntos si el empleado tiene sucursal_id = 1
                $empleado = Usuario::find($validated['empleado_id']);
                if ($empleado && $empleado->sucursal_id == 1) {
                    $puntos = PuntoEmpleado::where('usuario_id', $validated['empleado_id'])
                        ->whereDate('created_at', $validated['fecha'])
                        ->first();

                    if ($puntos) {
                        $puntos->increment('puntos', $puntosGanados);
                    } else {
                        PuntoEmpleado::create([
                            'usuario_id' => $validated['empleado_id'],
                            'puntos' => $puntosGanados,
                            'created_at' => $validated['fecha'],
                            'updated_at' => $validated['fecha'],
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Todas las ventas fueron registradas correctamente'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar las ventas',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    // Metodo para obtener las ventas
    public function ventasUsuarioDia(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'fecha' => 'required|date',
        ]);

        $ventas = Venta::with(['detalles.zapato', 'sucursal'])
            ->where('usuario_id', $request->usuario_id)
            ->whereDate('fecha', $request->fecha)
            ->get();

        return response()->json([
            'usuario_id' => $request->usuario_id,
            'fecha' => $request->fecha,
            'ventas' => $ventas,
            'total_ventas' => $ventas->sum('total')
        ]);
    }

    // Metodo para cortes de caja
    public function corteCaja(Request $request)
    {
        $validated = $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'corte_sucursal_id' => 'required|exists:sucursales,id',
            'turno' => 'required|in:MATUTINO,VESPERTINO',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'detalles' => 'required|string',
            'total_ventas' => 'required|numeric|min:0',
        ]);

        // Crear el corte de caja directamente
        $corte = CorteCaja::create([
            'usuario_id' => $validated['usuario_id'],
            'corte_sucursal_id' => $validated['corte_sucursal_id'],
            'turno' => $validated['turno'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'detalles' => $validated['detalles'],
            'total_ventas' => $validated['total_ventas'],
        ]);

        return response()->json([
            'message' => 'Corte de caja registrado correctamente',
            'corte' => $corte->load('usuario', 'sucursal'),
        ], 201);
    }

    // Metodo para obtener el corte de caja por usuario
    public function obtenerCorteCaja(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'fecha' => 'required|date',
        ]);

        $corte = CorteCaja::with('sucursal')
            ->where('usuario_id', $request->usuario_id)
            ->whereDate('fecha_inicio', $request->fecha)
            ->first();

        if (!$corte) {
            return response()->json([
                'usuario_id' => $request->usuario_id,
                'fecha' => $request->fecha,
                'corte' => null,
                'message' => 'No se encontró corte de caja para esta fecha.'
            ], 404);
        }

        return response()->json([
            'usuario_id' => $request->usuario_id,
            'fecha' => $request->fecha,
            'corte' => $corte,
        ]);
    }

    // Método para obtener las ventas por sucursal en un periodo específico
    public function ventasPorSucursal(Request $request)
    {
        $validated = $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $ventas = Venta::with(['detalles.zapato', 'sucursal'])
            ->whereHas('sucursal', function($query) use ($validated) {
                $query->where('id', $validated['sucursal_id']);
            })
            ->whereBetween('fecha', [$validated['fecha_inicio'], $validated['fecha_fin']])
            ->get();

        return response()->json([
            'ventas' => $ventas,
            'total_ventas' => $ventas->sum('total') // si quieres total ventas también
        ]);
    }

    // Metodo para realizar apartados
    public function registrarApartado(Request $request)
    {
        $validated = $request->validate([
            'zapato_id'             => 'required|exists:zapatos,id',
            'nombre_cliente'        => 'required|string|max:100',
            'telefono_cliente'      => 'nullable|string|max:20',
            'fecha_apartado'        => 'required|date',
            'fecha_limite'          => 'nullable|date|after_or_equal:fecha_apartado',
            'monto_apartado'        => 'required|numeric|min:0',
            'precio_zapato'         => 'required|numeric|min:0',
            'usuario_id'            => 'required|exists:usuarios,id',
            'usuario_bodega_id'     => 'required|exists:usuarios,id', // ✅ validación agregada
            'apartado_sucursal_id'  => 'required|exists:sucursales,id',
            'zapato_sucursal_id'    => 'required|exists:sucursales,id',
        ]);

        DB::beginTransaction();

        try {
            // 1. Verificar inventario
            $inventario = ZapatoSucursal::where('zapato_id', $validated['zapato_id'])
                ->where('sucursal_id', $validated['zapato_sucursal_id'])
                ->first();

            if (!$inventario || $inventario->unidades_disponibles < 1) {
                throw ValidationException::withMessages([
                    'zapato_id' => ['No hay stock disponible para este zapato en la sucursal especificada.']
                ]);
            }

            $monto_restante = $validated['precio_zapato'] - $validated['monto_apartado'];

            // 2. Crear apartado
            $apartado = Apartado::create([
                'zapato_id'            => $validated['zapato_id'],
                'nombre_cliente'       => $validated['nombre_cliente'],
                'telefono_cliente'     => $validated['telefono_cliente'] ?? null,
                'fecha_apartado'       => $validated['fecha_apartado'],
                'fecha_limite'         => $validated['fecha_limite'] ?? null,
                'monto_apartado'       => $validated['monto_apartado'],
                'monto_restante'       => $monto_restante,
                'monto_pagado'         => $validated['monto_apartado'],
                'precio_zapato'        => $validated['precio_zapato'],
                'estado'               => 'ACTIVO',
                'usuario_id'           => $validated['usuario_id'],
                'usuario_bodega_id'    => $validated['usuario_bodega_id'], // ✅ aquí también
                'apartado_sucursal_id' => $validated['apartado_sucursal_id'],
                'zapato_sucursal_id'   => $validated['zapato_sucursal_id'],
            ]);

            // 3. Registrar venta parcial
            $folio = 'AP-' . now()->timestamp . '-' . rand(100, 999);

            $venta = Venta::create([
                'folio' => $folio,
                'fecha' => $validated['fecha_apartado'],
                'usuario_id' => $validated['usuario_id'],
                'venta_sucursal_id' => $validated['apartado_sucursal_id'],
                'zapato_sucursal_id' => $validated['zapato_sucursal_id'],
                'metodo_pago' => 'EFECTIVO',
                'total' => $validated['monto_apartado'],
            ]);

            // 4. Detalle de la venta
            DetalleVenta::create([
                'venta_id' => $venta->id,
                'zapato_id' => $validated['zapato_id'],
                'cantidad' => 1,
                'precio_unitario' => $validated['precio_zapato'],
            ]);

            // 5. Descontar stock
            $inventario->decrement('unidades_disponibles');

            // 6. Otorgar puntos
            $empleado = Usuario::find($validated['usuario_id']);

            if ($empleado && $empleado->sucursal_id == 1) {
                $fecha = Carbon\Carbon::parse($validated['fecha_apartado'])->toDateString();

                $puntos = PuntoEmpleado::where('usuario_id', $empleado->id)
                    ->whereDate('created_at', $fecha)
                    ->first();

                if ($puntos) {
                    $puntos->increment('puntos', 1);
                } else {
                    PuntoEmpleado::create([
                        'usuario_id' => $empleado->id,
                        'puntos' => 1,
                        'created_at' => $fecha,
                        'updated_at' => $fecha,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Apartado registrado correctamente',
                'apartado' => $apartado
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al registrar el apartado',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    // Metodo para completar un apartado
    public function completarApartado(Request $request, $id)
    {
        $validated = $request->validate([
            'monto_pagado' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();

        try {
            $apartado = Apartado::where('id', $id)->where('estado', 'ACTIVO')->first();

            if (!$apartado) {
                return response()->json([
                    'message' => 'Apartado no encontrado o ya completado/cancelado'
                ], 404);
            }

            // Verificar que el nuevo pago no exceda el total
            $nuevo_pagado = $apartado->monto_pagado + $validated['monto_pagado'];
            $total_precio = $apartado->precio_zapato;

            if ($nuevo_pagado > $total_precio) {
                throw ValidationException::withMessages([
                    'monto_pagado' => ['El monto pagado excede el precio del zapato.']
                ]);
            }

            // Actualizar montos en apartado
            $apartado->monto_pagado = $nuevo_pagado;
            $apartado->monto_restante = $total_precio - $nuevo_pagado;

            if ($apartado->monto_restante <= 0) {
                $apartado->estado = 'COMPLETADO';
            }

            $apartado->save();

            // Si se completó el apartado, registrar la venta final
            if ($apartado->estado === 'COMPLETADO') {
                $folio = 'LIQ-' . now()->timestamp . '-' . rand(100, 999);

                $venta = Venta::create([
                    'folio' => $folio,
                    'fecha' => now()->toDateString(),
                    'usuario_id' => $apartado->usuario_id,
                    'venta_sucursal_id' => $apartado->apartado_sucursal_id,
                    'zapato_sucursal_id' => $apartado->zapato_sucursal_id,
                    'metodo_pago' => 'EFECTIVO',
                    'total' => $validated['monto_pagado'], // solo lo que pagó en esta liquidación
                ]);

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'zapato_id' => $apartado->zapato_id,
                    'cantidad' => 1,
                    'precio_unitario' => $apartado->precio_zapato,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pago registrado correctamente',
                'apartado' => $apartado
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al registrar el pago',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    // Metodo para cancelar un apartado
    public function cancelarApartado($id)
    {
        DB::beginTransaction();

        try {
            $apartado = Apartado::where('id', $id)->where('estado', 'ACTIVO')->first();

            if (!$apartado) {
                return response()->json([
                    'message' => 'Apartado no encontrado o ya cancelado/completado.'
                ], 404);
            }

            // Cambiar estado del apartado
            $apartado->estado = 'CANCELADO';
            $apartado->save();

            // Validar que el apartado tenga asignada una sucursal válida
            if (!$apartado->apartado_sucursal_id) {
                DB::rollBack();
                return response()->json([
                    'message' => 'El apartado no tiene asignada una sucursal válida, no se puede actualizar inventario.'
                ], 400);
            }

            // Aumentar inventario en la sucursal correcta
            $inventario = ZapatoSucursal::where('zapato_id', $apartado->zapato_id)
                ->where('sucursal_id', $apartado->apartado_sucursal_id)
                ->first();

            if ($inventario) {
                $inventario->increment('unidades_disponibles');
            } else {
                ZapatoSucursal::create([
                    'zapato_id' => $apartado->zapato_id,
                    'sucursal_id' => $apartado->apartado_sucursal_id,
                    'unidades_disponibles' => 1,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Apartado cancelado y zapato devuelto al inventario.',
                'apartado' => $apartado
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al cancelar el apartado.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    // Metodo para listar apartardos por estado y nombre del cliente
    public function listarApartados(Request $request)
    {
        $request->validate([
            'estado' => 'nullable|in:ACTIVO,COMPLETADO,CANCELADO',
            'nombre_cliente' => 'nullable|string|max:255',
        ]);

        $query = Apartado::query();

        // Filtro por estado si se proporciona
        if ($request->filled('estado')) {
            $query->where('estado', strtoupper($request->estado));
        }

        // Filtro por nombre del cliente si se proporciona
        if ($request->filled('nombre_cliente')) {
            $query->where('nombre_cliente', 'like', '%' . $request->nombre_cliente . '%');
        }

        $apartados = $query->with(['zapato', 'usuario', 'sucursalApartado', 'sucursalZapato'])->latest()->get();

        return response()->json([
            'message' => 'Apartados encontrados',
            'data' => $apartados
        ]);
    }

}
