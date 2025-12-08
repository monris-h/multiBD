<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CategoriaController extends Controller
{
    /**
     * Listar todas las categorías activas
     * GET /api/categorias
     */
    public function index(Request $request): JsonResponse
    {
        $query = Categoria::query();

        // Filtrar solo activos por defecto
        if ($request->get('incluir_inactivos') !== 'true') {
            $query->activos();
        }

        // Búsqueda por nombre
        if ($request->has('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        // Ordenamiento
        $orderBy = $request->get('ordenar_por', 'created_at');
        $orderDir = $request->get('orden', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Paginación
        $perPage = $request->get('per_page', 15);
        $categorias = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $categorias,
            'message' => 'Categorías obtenidas exitosamente'
        ]);
    }

    /**
     * Crear una nueva categoría
     * POST /api/categorias
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:categorias,nombre',
                'descripcion' => 'nullable|string',
                'activo' => 'boolean'
            ]);

            $categoria = Categoria::create($validated);

            // Registrar en logs (MongoDB)
            Log::registrar('crear', 'categorias', $categoria->id, null, $categoria->toArray());

            return response()->json([
                'success' => true,
                'data' => $categoria,
                'message' => 'Categoría creada exitosamente'
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
     * Mostrar una categoría específica
     * GET /api/categorias/{id}
     */
    public function show($id): JsonResponse
    {
        $categoria = Categoria::with('productos')->find($id);

        if (!$categoria) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $categoria,
            'message' => 'Categoría obtenida exitosamente'
        ]);
    }

    /**
     * Actualizar una categoría
     * PATCH /api/categorias/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no encontrada'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'nombre' => 'sometimes|string|max:255|unique:categorias,nombre,' . $id,
                'descripcion' => 'nullable|string',
                'activo' => 'boolean'
            ]);

            $datosAnteriores = $categoria->toArray();
            $categoria->update($validated);

            // Registrar en logs (MongoDB)
            Log::registrar('actualizar', 'categorias', $categoria->id, $datosAnteriores, $categoria->toArray());

            return response()->json([
                'success' => true,
                'data' => $categoria,
                'message' => 'Categoría actualizada exitosamente'
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
     * Eliminar una categoría (borrado lógico)
     * DELETE /api/categorias/{id}
     */
    public function destroy($id): JsonResponse
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no encontrada'
            ], 404);
        }

        $datosAnteriores = $categoria->toArray();
        
        // Borrado lógico (soft delete)
        $categoria->activo = false;
        $categoria->save();
        $categoria->delete();

        // Registrar en logs (MongoDB)
        Log::registrar('eliminar', 'categorias', $categoria->id, $datosAnteriores, null);

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada exitosamente'
        ]);
    }

    /**
     * Restaurar una categoría eliminada
     * PATCH /api/categorias/{id}/restaurar
     */
    public function restore($id): JsonResponse
    {
        $categoria = Categoria::withTrashed()->find($id);

        if (!$categoria) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no encontrada'
            ], 404);
        }

        $categoria->restore();
        $categoria->activo = true;
        $categoria->save();

        // Registrar en logs (MongoDB)
        Log::registrar('actualizar', 'categorias', $categoria->id, null, $categoria->toArray());

        return response()->json([
            'success' => true,
            'data' => $categoria,
            'message' => 'Categoría restaurada exitosamente'
        ]);
    }
}
