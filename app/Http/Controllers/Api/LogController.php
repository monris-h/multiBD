<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LogController extends Controller
{
    /**
     * Listar todos los logs activos
     * GET /api/logs
     */
    public function index(Request $request): JsonResponse
    {
        $query = Log::query();

        // Filtrar solo activos por defecto
        if ($request->get('incluir_inactivos') !== 'true') {
            $query->activos();
        }

        // Filtrar por entidad
        if ($request->has('entidad')) {
            $query->porEntidad($request->entidad);
        }

        // Filtrar por acción
        if ($request->has('accion')) {
            $query->porAccion($request->accion);
        }

        // Filtrar por usuario
        if ($request->has('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        // Filtrar por entidad_id
        if ($request->has('entidad_id')) {
            $query->where('entidad_id', $request->entidad_id);
        }

        // Rango de fechas
        if ($request->has('fecha_desde')) {
            $query->where('created_at', '>=', new \DateTime($request->fecha_desde));
        }
        if ($request->has('fecha_hasta')) {
            $query->where('created_at', '<=', new \DateTime($request->fecha_hasta));
        }

        // Ordenamiento (por defecto más recientes primero)
        $orderBy = $request->get('ordenar_por', 'created_at');
        $orderDir = $request->get('orden', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Paginación
        $perPage = $request->get('per_page', 15);
        $logs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs,
            'message' => 'Logs obtenidos exitosamente'
        ]);
    }

    /**
     * Crear un nuevo log
     * POST /api/logs
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'accion' => 'required|string|in:crear,actualizar,eliminar,login,logout',
                'entidad' => 'required|string|max:100',
                'entidad_id' => 'nullable|string',
                'usuario_id' => 'nullable|integer',
                'datos_anteriores' => 'nullable|array',
                'datos_nuevos' => 'nullable|array',
                'ip_address' => 'nullable|string|max:45',
                'user_agent' => 'nullable|string',
                'activo' => 'boolean'
            ]);

            // Añadir información de la petición si no se proporcionó
            $validated['ip_address'] = $validated['ip_address'] ?? request()->ip();
            $validated['user_agent'] = $validated['user_agent'] ?? request()->userAgent();
            $validated['activo'] = $validated['activo'] ?? true;

            $log = Log::create($validated);

            return response()->json([
                'success' => true,
                'data' => $log,
                'message' => 'Log creado exitosamente'
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
     * Mostrar un log específico
     * GET /api/logs/{id}
     */
    public function show($id): JsonResponse
    {
        $log = Log::find($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Log no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $log,
            'message' => 'Log obtenido exitosamente'
        ]);
    }

    /**
     * Actualizar un log
     * PATCH /api/logs/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $log = Log::find($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Log no encontrado'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'accion' => 'sometimes|string|in:crear,actualizar,eliminar,login,logout',
                'entidad' => 'sometimes|string|max:100',
                'entidad_id' => 'nullable|string',
                'usuario_id' => 'nullable|integer',
                'datos_anteriores' => 'nullable|array',
                'datos_nuevos' => 'nullable|array',
                'activo' => 'boolean'
            ]);

            $log->update($validated);

            return response()->json([
                'success' => true,
                'data' => $log,
                'message' => 'Log actualizado exitosamente'
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
     * Eliminar un log (borrado lógico)
     * DELETE /api/logs/{id}
     */
    public function destroy($id): JsonResponse
    {
        $log = Log::find($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Log no encontrado'
            ], 404);
        }

        // Borrado lógico (soft delete)
        $log->activo = false;
        $log->save();
        $log->delete();

        return response()->json([
            'success' => true,
            'message' => 'Log eliminado exitosamente'
        ]);
    }

    /**
     * Restaurar un log eliminado
     * PATCH /api/logs/{id}/restaurar
     */
    public function restore($id): JsonResponse
    {
        $log = Log::withTrashed()->find($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Log no encontrado'
            ], 404);
        }

        $log->restore();
        $log->activo = true;
        $log->save();

        return response()->json([
            'success' => true,
            'data' => $log,
            'message' => 'Log restaurado exitosamente'
        ]);
    }

    /**
     * Obtener acciones disponibles
     * GET /api/logs/acciones
     */
    public function acciones(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Log::ACCIONES,
            'message' => 'Acciones obtenidas exitosamente'
        ]);
    }
}
