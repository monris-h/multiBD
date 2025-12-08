<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ClienteController extends Controller
{
    /**
     * Listar todos los clientes activos
     * GET /api/clientes
     */
    public function index(Request $request): JsonResponse
    {
        $query = Cliente::query();

        // Filtrar solo activos por defecto
        if ($request->get('incluir_inactivos') !== 'true') {
            $query->activos();
        }

        // Búsqueda por nombre, apellido o email
        if ($request->has('buscar')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->buscar . '%')
                  ->orWhere('apellido', 'like', '%' . $request->buscar . '%')
                  ->orWhere('email', 'like', '%' . $request->buscar . '%');
            });
        }

        // Filtrar por ciudad
        if ($request->has('ciudad')) {
            $query->where('ciudad', $request->ciudad);
        }

        // Filtrar por estado
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        // Ordenamiento
        $orderBy = $request->get('ordenar_por', 'created_at');
        $orderDir = $request->get('orden', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Paginación
        $perPage = $request->get('per_page', 15);
        $clientes = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $clientes,
            'message' => 'Clientes obtenidos exitosamente'
        ]);
    }

    /**
     * Crear un nuevo cliente
     * POST /api/clientes
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'apellido' => 'required|string|max:255',
                'email' => 'required|email|unique:clientes,email',
                'telefono' => 'nullable|string|max:20',
                'direccion' => 'nullable|string',
                'ciudad' => 'nullable|string|max:100',
                'estado' => 'nullable|string|max:100',
                'codigo_postal' => 'nullable|string|max:10',
                'activo' => 'boolean'
            ]);

            $cliente = Cliente::create($validated);

            // Registrar en logs (MongoDB)
            Log::registrar('crear', 'clientes', $cliente->id, null, $cliente->toArray());

            return response()->json([
                'success' => true,
                'data' => $cliente,
                'message' => 'Cliente creado exitosamente'
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
     * Mostrar un cliente específico
     * GET /api/clientes/{id}
     */
    public function show($id): JsonResponse
    {
        $cliente = Cliente::with('ordenes')->find($id);

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $cliente,
            'message' => 'Cliente obtenido exitosamente'
        ]);
    }

    /**
     * Actualizar un cliente
     * PATCH /api/clientes/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'nombre' => 'sometimes|string|max:255',
                'apellido' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:clientes,email,' . $id,
                'telefono' => 'nullable|string|max:20',
                'direccion' => 'nullable|string',
                'ciudad' => 'nullable|string|max:100',
                'estado' => 'nullable|string|max:100',
                'codigo_postal' => 'nullable|string|max:10',
                'activo' => 'boolean'
            ]);

            $datosAnteriores = $cliente->toArray();
            $cliente->update($validated);

            // Registrar en logs (MongoDB)
            Log::registrar('actualizar', 'clientes', $cliente->id, $datosAnteriores, $cliente->toArray());

            return response()->json([
                'success' => true,
                'data' => $cliente,
                'message' => 'Cliente actualizado exitosamente'
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
     * Eliminar un cliente (borrado lógico)
     * DELETE /api/clientes/{id}
     */
    public function destroy($id): JsonResponse
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ], 404);
        }

        $datosAnteriores = $cliente->toArray();
        
        // Borrado lógico (soft delete)
        $cliente->activo = false;
        $cliente->save();
        $cliente->delete();

        // Registrar en logs (MongoDB)
        Log::registrar('eliminar', 'clientes', $cliente->id, $datosAnteriores, null);

        return response()->json([
            'success' => true,
            'message' => 'Cliente eliminado exitosamente'
        ]);
    }

    /**
     * Restaurar un cliente eliminado
     * PATCH /api/clientes/{id}/restaurar
     */
    public function restore($id): JsonResponse
    {
        $cliente = Cliente::withTrashed()->find($id);

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ], 404);
        }

        $cliente->restore();
        $cliente->activo = true;
        $cliente->save();

        // Registrar en logs (MongoDB)
        Log::registrar('actualizar', 'clientes', $cliente->id, null, $cliente->toArray());

        return response()->json([
            'success' => true,
            'data' => $cliente,
            'message' => 'Cliente restaurado exitosamente'
        ]);
    }
}
