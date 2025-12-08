<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class OrdenController extends Controller
{
    /**
     * Listar todas las órdenes activas
     * GET /api/ordenes
     */
    public function index(Request $request): JsonResponse
    {
        $query = Orden::with('cliente');

        // Filtrar solo activas por defecto
        if ($request->get('incluir_inactivos') !== 'true') {
            $query->activos();
        }

        // Filtrar por cliente
        if ($request->has('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        // Filtrar por estado
        if ($request->has('estado')) {
            $query->porEstado($request->estado);
        }

        // Búsqueda por número de orden
        if ($request->has('buscar')) {
            $query->where('numero_orden', 'like', '%' . $request->buscar . '%');
        }

        // Rango de fechas
        if ($request->has('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->has('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        // Rango de totales
        if ($request->has('total_min')) {
            $query->where('total', '>=', $request->total_min);
        }
        if ($request->has('total_max')) {
            $query->where('total', '<=', $request->total_max);
        }

        // Ordenamiento
        $orderBy = $request->get('ordenar_por', 'created_at');
        $orderDir = $request->get('orden', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Paginación
        $perPage = $request->get('per_page', 15);
        $ordenes = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $ordenes,
            'message' => 'Órdenes obtenidas exitosamente'
        ]);
    }

    /**
     * Crear una nueva orden
     * POST /api/ordenes
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'subtotal' => 'required|numeric|min:0',
                'impuestos' => 'nullable|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'estado' => 'nullable|in:pendiente,procesando,enviado,entregado,cancelado',
                'notas' => 'nullable|string',
                'fecha_entrega' => 'nullable|date',
                'activo' => 'boolean'
            ]);

            // Generar número de orden único
            $validated['numero_orden'] = Orden::generarNumeroOrden();

            // Establecer valores por defecto
            $validated['impuestos'] = $validated['impuestos'] ?? 0;
            $validated['estado'] = $validated['estado'] ?? 'pendiente';

            $orden = Orden::create($validated);

            // Registrar en logs (MongoDB)
            Log::registrar('crear', 'ordenes', $orden->id, null, $orden->toArray());

            return response()->json([
                'success' => true,
                'data' => $orden->load('cliente'),
                'message' => 'Orden creada exitosamente'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Error de validación'
            ], 422);
        }
    }

    /**
     * Mostrar una orden específica
     * GET /api/ordenes/{id}
     */
    public function show($id): JsonResponse
    {
        $orden = Orden::with('cliente')->find($id);

        if (!$orden) {
            return response()->json([
                'success' => false,
                'message' => 'Orden no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $orden,
            'message' => 'Orden obtenida exitosamente'
        ]);
    }

    /**
     * Actualizar una orden
     * PATCH /api/ordenes/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $orden = Orden::find($id);

        if (!$orden) {
            return response()->json([
                'success' => false,
                'message' => 'Orden no encontrada'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'cliente_id' => 'sometimes|exists:clientes,id',
                'subtotal' => 'sometimes|numeric|min:0',
                'impuestos' => 'nullable|numeric|min:0',
                'total' => 'sometimes|numeric|min:0',
                'estado' => 'sometimes|in:pendiente,procesando,enviado,entregado,cancelado',
                'notas' => 'nullable|string',
                'fecha_entrega' => 'nullable|date',
                'activo' => 'boolean'
            ]);

            $datosAnteriores = $orden->toArray();
            $orden->update($validated);

            // Registrar en logs (MongoDB)
            Log::registrar('actualizar', 'ordenes', $orden->id, $datosAnteriores, $orden->toArray());

            return response()->json([
                'success' => true,
                'data' => $orden->load('cliente'),
                'message' => 'Orden actualizada exitosamente'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Error de validación'
            ], 422);
        }
    }

    /**
     * Eliminar una orden (borrado lógico)
     * DELETE /api/ordenes/{id}
     */
    public function destroy($id): JsonResponse
    {
        $orden = Orden::find($id);

        if (!$orden) {
            return response()->json([
                'success' => false,
                'message' => 'Orden no encontrada'
            ], 404);
        }

        $datosAnteriores = $orden->toArray();
        
        // Borrado lógico (soft delete)
        $orden->activo = false;
        $orden->save();
        $orden->delete();

        // Registrar en logs (MongoDB)
        Log::registrar('eliminar', 'ordenes', $orden->id, $datosAnteriores, null);

        return response()->json([
            'success' => true,
            'message' => 'Orden eliminada exitosamente'
        ]);
    }

    /**
     * Restaurar una orden eliminada
     * PATCH /api/ordenes/{id}/restaurar
     */
    public function restore($id): JsonResponse
    {
        $orden = Orden::withTrashed()->find($id);

        if (!$orden) {
            return response()->json([
                'success' => false,
                'message' => 'Orden no encontrada'
            ], 404);
        }

        $orden->restore();
        $orden->activo = true;
        $orden->save();

        // Registrar en logs (MongoDB)
        Log::registrar('actualizar', 'ordenes', $orden->id, null, $orden->toArray());

        return response()->json([
            'success' => true,
            'data' => $orden->load('cliente'),
            'message' => 'Orden restaurada exitosamente'
        ]);
    }

    /**
     * Obtener estados disponibles
     * GET /api/ordenes/estados
     */
    public function estados(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Orden::ESTADOS,
            'message' => 'Estados obtenidos exitosamente'
        ]);
    }
}
