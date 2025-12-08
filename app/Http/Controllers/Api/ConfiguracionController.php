<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ConfiguracionService;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ConfiguracionController extends Controller
{
    protected $configuracionService;

    public function __construct(ConfiguracionService $configuracionService)
    {
        $this->configuracionService = $configuracionService;
    }

    /**
     * Listar todas las configuraciones activas
     * GET /api/configuraciones
     */
    public function index(Request $request): JsonResponse
    {
        $configuraciones = $this->configuracionService->listar();

        // Filtrar por tipo si se especifica
        if ($request->has('tipo')) {
            $configuraciones = array_filter($configuraciones, function($config) use ($request) {
                return isset($config['tipo']) && $config['tipo'] === $request->tipo;
            });
        }

        // Búsqueda por clave
        if ($request->has('buscar')) {
            $buscar = strtolower($request->buscar);
            $configuraciones = array_filter($configuraciones, function($config) use ($buscar) {
                return isset($config['clave']) && str_contains(strtolower($config['clave']), $buscar);
            });
        }

        return response()->json([
            'success' => true,
            'data' => array_values($configuraciones),
            'total' => count($configuraciones),
            'message' => 'Configuraciones obtenidas exitosamente'
        ]);
    }

    /**
     * Crear una nueva configuración
     * POST /api/configuraciones
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'clave' => 'required|string|max:100|regex:/^[a-zA-Z0-9_-]+$/',
                'valor' => 'required',
                'descripcion' => 'nullable|string|max:500',
                'tipo' => 'nullable|string|in:string,integer,boolean,json,array'
            ]);

            // Verificar si ya existe
            $existing = $this->configuracionService->obtener($validated['clave']);
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una configuración con esta clave'
                ], 422);
            }

            $configuracion = $this->configuracionService->crear(
                $validated['clave'],
                [
                    'valor' => $validated['valor'],
                    'descripcion' => $validated['descripcion'] ?? null,
                    'tipo' => $validated['tipo'] ?? 'string'
                ]
            );

            // Registrar en logs (MongoDB)
            Log::registrar('crear', 'configuraciones', $validated['clave'], null, $configuracion);

            return response()->json([
                'success' => true,
                'data' => $configuracion,
                'message' => 'Configuración creada exitosamente'
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
     * Mostrar una configuración específica
     * GET /api/configuraciones/{clave}
     */
    public function show($clave): JsonResponse
    {
        $configuracion = $this->configuracionService->obtener($clave);

        if (!$configuracion) {
            return response()->json([
                'success' => false,
                'message' => 'Configuración no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => array_merge(['clave' => $clave], $configuracion),
            'message' => 'Configuración obtenida exitosamente'
        ]);
    }

    /**
     * Actualizar una configuración
     * PATCH /api/configuraciones/{clave}
     */
    public function update(Request $request, $clave): JsonResponse
    {
        $configActual = $this->configuracionService->obtener($clave);

        if (!$configActual) {
            return response()->json([
                'success' => false,
                'message' => 'Configuración no encontrada'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'valor' => 'sometimes|required',
                'descripcion' => 'nullable|string|max:500',
                'tipo' => 'nullable|string|in:string,integer,boolean,json,array'
            ]);

            $datosAnteriores = array_merge(['clave' => $clave], $configActual);
            $configuracion = $this->configuracionService->actualizar($clave, $validated);

            // Registrar en logs (MongoDB)
            Log::registrar('actualizar', 'configuraciones', $clave, $datosAnteriores, $configuracion);

            return response()->json([
                'success' => true,
                'data' => $configuracion,
                'message' => 'Configuración actualizada exitosamente'
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
     * Eliminar una configuración (borrado lógico)
     * DELETE /api/configuraciones/{clave}
     */
    public function destroy($clave): JsonResponse
    {
        $configActual = $this->configuracionService->obtener($clave);

        if (!$configActual) {
            return response()->json([
                'success' => false,
                'message' => 'Configuración no encontrada'
            ], 404);
        }

        $datosAnteriores = array_merge(['clave' => $clave], $configActual);
        $result = $this->configuracionService->eliminar($clave);

        if ($result) {
            // Registrar en logs (MongoDB)
            Log::registrar('eliminar', 'configuraciones', $clave, $datosAnteriores, null);

            return response()->json([
                'success' => true,
                'message' => 'Configuración eliminada exitosamente'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al eliminar la configuración'
        ], 500);
    }

    /**
     * Restaurar una configuración eliminada
     * PATCH /api/configuraciones/{clave}/restaurar
     */
    public function restore($clave): JsonResponse
    {
        $configuracion = $this->configuracionService->restaurar($clave);

        if (!$configuracion) {
            return response()->json([
                'success' => false,
                'message' => 'Configuración no encontrada'
            ], 404);
        }

        // Registrar en logs (MongoDB)
        Log::registrar('actualizar', 'configuraciones', $clave, null, $configuracion);

        return response()->json([
            'success' => true,
            'data' => $configuracion,
            'message' => 'Configuración restaurada exitosamente'
        ]);
    }

    /**
     * Listar configuraciones eliminadas
     * GET /api/configuraciones/eliminadas
     */
    public function deleted(): JsonResponse
    {
        $configuraciones = $this->configuracionService->listarEliminadas();

        return response()->json([
            'success' => true,
            'data' => $configuraciones,
            'total' => count($configuraciones),
            'message' => 'Configuraciones eliminadas obtenidas exitosamente'
        ]);
    }
}
