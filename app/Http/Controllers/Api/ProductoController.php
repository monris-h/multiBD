<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ProductoController extends Controller
{
    /**
     * Listar todos los productos activos
     * GET /api/productos
     */
    public function index(Request $request): JsonResponse
    {
        $query = Producto::with('categoria');

        // Filtrar solo activos por defecto
        if ($request->get('incluir_inactivos') !== 'true') {
            $query->activos();
        }

        // Filtrar por categoría
        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // Filtrar por stock
        if ($request->get('con_stock') === 'true') {
            $query->conStock();
        }

        // Búsqueda por nombre o SKU
        if ($request->has('buscar')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->buscar . '%')
                  ->orWhere('sku', 'like', '%' . $request->buscar . '%');
            });
        }

        // Rango de precios
        if ($request->has('precio_min')) {
            $query->where('precio', '>=', $request->precio_min);
        }
        if ($request->has('precio_max')) {
            $query->where('precio', '<=', $request->precio_max);
        }

        // Ordenamiento
        $orderBy = $request->get('ordenar_por', 'created_at');
        $orderDir = $request->get('orden', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Paginación
        $perPage = $request->get('per_page', 15);
        $productos = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $productos,
            'message' => 'Productos obtenidos exitosamente'
        ]);
    }

    /**
     * Crear un nuevo producto
     * POST /api/productos
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'precio' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'sku' => 'required|string|max:50|unique:productos,sku',
                'categoria_id' => 'required|exists:categorias,id',
                'activo' => 'boolean'
            ]);

            $producto = Producto::create($validated);

            // Registrar en logs (MongoDB)
            Log::registrar('crear', 'productos', $producto->id, null, $producto->toArray());

            return response()->json([
                'success' => true,
                'data' => $producto->load('categoria'),
                'message' => 'Producto creado exitosamente'
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
     * Mostrar un producto específico
     * GET /api/productos/{id}
     */
    public function show($id): JsonResponse
    {
        $producto = Producto::with('categoria')->find($id);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $producto,
            'message' => 'Producto obtenido exitosamente'
        ]);
    }

    /**
     * Actualizar un producto
     * PATCH /api/productos/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'nombre' => 'sometimes|string|max:255',
                'descripcion' => 'nullable|string',
                'precio' => 'sometimes|numeric|min:0',
                'stock' => 'sometimes|integer|min:0',
                'sku' => 'sometimes|string|max:50|unique:productos,sku,' . $id,
                'categoria_id' => 'sometimes|exists:categorias,id',
                'activo' => 'boolean'
            ]);

            $datosAnteriores = $producto->toArray();
            $producto->update($validated);

            // Registrar en logs (MongoDB)
            Log::registrar('actualizar', 'productos', $producto->id, $datosAnteriores, $producto->toArray());

            return response()->json([
                'success' => true,
                'data' => $producto->load('categoria'),
                'message' => 'Producto actualizado exitosamente'
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
     * Eliminar un producto (borrado lógico)
     * DELETE /api/productos/{id}
     */
    public function destroy($id): JsonResponse
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        $datosAnteriores = $producto->toArray();
        
        // Borrado lógico (soft delete)
        $producto->activo = false;
        $producto->save();
        $producto->delete();

        // Registrar en logs (MongoDB)
        Log::registrar('eliminar', 'productos', $producto->id, $datosAnteriores, null);

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado exitosamente'
        ]);
    }

    /**
     * Restaurar un producto eliminado
     * PATCH /api/productos/{id}/restaurar
     */
    public function restore($id): JsonResponse
    {
        $producto = Producto::withTrashed()->find($id);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        $producto->restore();
        $producto->activo = true;
        $producto->save();

        // Registrar en logs (MongoDB)
        Log::registrar('actualizar', 'productos', $producto->id, null, $producto->toArray());

        return response()->json([
            'success' => true,
            'data' => $producto->load('categoria'),
            'message' => 'Producto restaurado exitosamente'
        ]);
    }
}
