<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SesionCacheService;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class SesionCacheController extends Controller
{
    protected $sesionService;

    public function __construct(SesionCacheService $sesionService)
    {
        $this->sesionService = $sesionService;
    }

    /**
     * Listar todas las sesiones activas
     * GET /api/sesiones
     */
    public function index(Request $request): JsonResponse
    {
        $sesiones = $this->sesionService->getAll();

        // Filtrar por usuario
        if ($request->has('usuario_id')) {
            $sesiones = array_filter($sesiones, function($sesion) use ($request) {
                return isset($sesion['usuario_id']) && $sesion['usuario_id'] == $request->usuario_id;
            });
        }

        return response()->json([
            'success' => true,
            'data' => array_values($sesiones),
            'total' => count($sesiones),
            'message' => 'Sesiones obtenidas exitosamente'
        ]);
    }

    /**
     * Crear una nueva sesión
     * POST /api/sesiones
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'usuario_id' => 'required|integer',
                'datos' => 'nullable|array'
            ]);

            $sesion = $this->sesionService->create(
                $validated['usuario_id'],
                $validated['datos'] ?? []
            );

            // Registrar en logs (MongoDB)
            Log::registrar('crear', 'sesiones', $sesion['id'], null, $sesion);

            return response()->json([
                'success' => true,
                'data' => $sesion,
                'message' => 'Sesión creada exitosamente'
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
     * Mostrar una sesión específica
     * GET /api/sesiones/{id}
     */
    public function show($id): JsonResponse
    {
        $sesion = $this->sesionService->get($id);

        if (!$sesion) {
            return response()->json([
                'success' => false,
                'message' => 'Sesión no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => array_merge(['id' => $id], $sesion),
            'message' => 'Sesión obtenida exitosamente'
        ]);
    }

    /**
     * Actualizar una sesión
     * PATCH /api/sesiones/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $sesionActual = $this->sesionService->get($id);

        if (!$sesionActual) {
            return response()->json([
                'success' => false,
                'message' => 'Sesión no encontrada'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'datos' => 'nullable|array',
                'usuario_id' => 'sometimes|integer'
            ]);

            $datosAnteriores = array_merge(['id' => $id], $sesionActual);
            $sesion = $this->sesionService->update($id, $validated);

            // Registrar en logs (MongoDB)
            Log::registrar('actualizar', 'sesiones', $id, $datosAnteriores, $sesion);

            return response()->json([
                'success' => true,
                'data' => $sesion,
                'message' => 'Sesión actualizada exitosamente'
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
     * Eliminar una sesión (borrado lógico)
     * DELETE /api/sesiones/{id}
     */
    public function destroy($id): JsonResponse
    {
        $sesionActual = $this->sesionService->get($id);

        if (!$sesionActual) {
            return response()->json([
                'success' => false,
                'message' => 'Sesión no encontrada'
            ], 404);
        }

        $datosAnteriores = array_merge(['id' => $id], $sesionActual);
        $result = $this->sesionService->delete($id);

        if ($result) {
            // Registrar en logs (MongoDB)
            Log::registrar('eliminar', 'sesiones', $id, $datosAnteriores, null);

            return response()->json([
                'success' => true,
                'message' => 'Sesión eliminada exitosamente'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al eliminar la sesión'
        ], 500);
    }

    /**
     * Restaurar una sesión eliminada
     * PATCH /api/sesiones/{id}/restaurar
     */
    public function restore($id): JsonResponse
    {
        $sesion = $this->sesionService->restore($id);

        if (!$sesion) {
            return response()->json([
                'success' => false,
                'message' => 'Sesión no encontrada'
            ], 404);
        }

        // Registrar en logs (MongoDB)
        Log::registrar('actualizar', 'sesiones', $id, null, $sesion);

        return response()->json([
            'success' => true,
            'data' => $sesion,
            'message' => 'Sesión restaurada exitosamente'
        ]);
    }

    /**
     * Listar sesiones eliminadas
     * GET /api/sesiones/eliminadas
     */
    public function deleted(): JsonResponse
    {
        $sesiones = $this->sesionService->getDeleted();

        return response()->json([
            'success' => true,
            'data' => $sesiones,
            'total' => count($sesiones),
            'message' => 'Sesiones eliminadas obtenidas exitosamente'
        ]);
    }

    /**
     * Obtener sesiones por usuario
     * GET /api/sesiones/usuario/{usuario_id}
     */
    public function byUsuario($usuarioId): JsonResponse
    {
        $sesiones = $this->sesionService->getByUsuario($usuarioId);

        return response()->json([
            'success' => true,
            'data' => array_values($sesiones),
            'total' => count($sesiones),
            'message' => 'Sesiones del usuario obtenidas exitosamente'
        ]);
    }
}
