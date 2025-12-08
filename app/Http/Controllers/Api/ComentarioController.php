<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ComentarioController extends Controller
{
    /**
     * Listar todos los comentarios activos
     * GET /api/comentarios
     */
    public function index(Request $request): JsonResponse
    {
        $query = Comentario::query();

        // Filtrar solo activos por defecto
        if ($request->get('incluir_inactivos') !== 'true') {
            $query->activos();
        }

        // Filtrar por entidad
        if ($request->has('entidad')) {
            $query->porEntidad($request->entidad, $request->entidad_id);
        }

        // Filtrar por usuario
        if ($request->has('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        // Filtrar por calificación mínima
        if ($request->has('calificacion_min')) {
            $query->calificacionMinima($request->calificacion_min);
        }

        // Búsqueda en contenido
        if ($request->has('buscar')) {
            $query->where('contenido', 'like', '%' . $request->buscar . '%');
        }

        // Ordenamiento
        $orderBy = $request->get('ordenar_por', 'created_at');
        $orderDir = $request->get('orden', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Paginación
        $perPage = $request->get('per_page', 15);
        $comentarios = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $comentarios,
            'message' => 'Comentarios obtenidos exitosamente'
        ]);
    }

    /**
     * Crear un nuevo comentario
     * POST /api/comentarios
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'contenido' => 'required|string|min:1',
                'entidad' => 'required|string|max:100',
                'entidad_id' => 'required|string',
                'usuario_id' => 'nullable|integer',
                'usuario_nombre' => 'nullable|string|max:255',
                'calificacion' => 'nullable|integer|min:1|max:5',
                'metadata' => 'nullable|array',
                'activo' => 'boolean'
            ]);

            $validated['activo'] = $validated['activo'] ?? true;

            $comentario = Comentario::create($validated);

            // Registrar en logs
            Log::registrar('crear', 'comentarios', (string)$comentario->_id, null, $comentario->toArray());

            return response()->json([
                'success' => true,
                'data' => $comentario,
                'message' => 'Comentario creado exitosamente'
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
     * Mostrar un comentario específico
     * GET /api/comentarios/{id}
     */
    public function show($id): JsonResponse
    {
        $comentario = Comentario::find($id);

        if (!$comentario) {
            return response()->json([
                'success' => false,
                'message' => 'Comentario no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $comentario,
            'message' => 'Comentario obtenido exitosamente'
        ]);
    }

    /**
     * Actualizar un comentario
     * PATCH /api/comentarios/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $comentario = Comentario::find($id);

        if (!$comentario) {
            return response()->json([
                'success' => false,
                'message' => 'Comentario no encontrado'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'contenido' => 'sometimes|string|min:1',
                'entidad' => 'sometimes|string|max:100',
                'entidad_id' => 'sometimes|string',
                'usuario_id' => 'nullable|integer',
                'usuario_nombre' => 'nullable|string|max:255',
                'calificacion' => 'nullable|integer|min:1|max:5',
                'metadata' => 'nullable|array',
                'activo' => 'boolean'
            ]);

            $datosAnteriores = $comentario->toArray();
            $comentario->update($validated);

            // Registrar en logs
            Log::registrar('actualizar', 'comentarios', (string)$comentario->_id, $datosAnteriores, $comentario->toArray());

            return response()->json([
                'success' => true,
                'data' => $comentario,
                'message' => 'Comentario actualizado exitosamente'
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
     * Eliminar un comentario (borrado lógico)
     * DELETE /api/comentarios/{id}
     */
    public function destroy($id): JsonResponse
    {
        $comentario = Comentario::find($id);

        if (!$comentario) {
            return response()->json([
                'success' => false,
                'message' => 'Comentario no encontrado'
            ], 404);
        }

        $datosAnteriores = $comentario->toArray();

        // Borrado lógico (soft delete)
        $comentario->activo = false;
        $comentario->save();
        $comentario->delete();

        // Registrar en logs
        Log::registrar('eliminar', 'comentarios', (string)$comentario->_id, $datosAnteriores, null);

        return response()->json([
            'success' => true,
            'message' => 'Comentario eliminado exitosamente'
        ]);
    }

    /**
     * Restaurar un comentario eliminado
     * PATCH /api/comentarios/{id}/restaurar
     */
    public function restore($id): JsonResponse
    {
        $comentario = Comentario::withTrashed()->find($id);

        if (!$comentario) {
            return response()->json([
                'success' => false,
                'message' => 'Comentario no encontrado'
            ], 404);
        }

        $comentario->restore();
        $comentario->activo = true;
        $comentario->save();

        // Registrar en logs
        Log::registrar('actualizar', 'comentarios', (string)$comentario->_id, null, $comentario->toArray());

        return response()->json([
            'success' => true,
            'data' => $comentario,
            'message' => 'Comentario restaurado exitosamente'
        ]);
    }

    /**
     * Obtener promedio de calificaciones para una entidad
     * GET /api/comentarios/promedio/{entidad}/{entidad_id}
     */
    public function promedio($entidad, $entidadId): JsonResponse
    {
        $promedio = Comentario::promedioCalificacion($entidad, $entidadId);
        $total = Comentario::activos()->porEntidad($entidad, $entidadId)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'entidad' => $entidad,
                'entidad_id' => $entidadId,
                'promedio' => round($promedio ?? 0, 2),
                'total_comentarios' => $total
            ],
            'message' => 'Promedio obtenido exitosamente'
        ]);
    }
}
