<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class SesionCacheService
{
    protected $prefix = 'sesion:';

    /**
     * Obtener todas las sesiones activas
     */
    public function getAll()
    {
        $keys = Redis::keys($this->prefix . '*');
        $sesiones = [];

        foreach ($keys as $key) {
            $cleanKey = preg_replace('/^[^:]+:/', '', $key);
            $id = str_replace($this->prefix, '', $cleanKey);
            $data = $this->get($id);
            if ($data && (!isset($data['activo']) || $data['activo'] === true)) {
                $sesiones[] = array_merge(['id' => $id], $data);
            }
        }

        return $sesiones;
    }

    /**
     * Obtener una sesión por ID
     */
    public function get($id)
    {
        $data = Redis::get($this->prefix . $id);
        
        if (!$data) {
            return null;
        }

        $decoded = json_decode($data, true);
        
        if (isset($decoded['activo']) && $decoded['activo'] === false) {
            return null;
        }

        return $decoded;
    }

    /**
     * Crear una nueva sesión
     */
    public function create($usuarioId, $datos = [])
    {
        $id = uniqid('ses_', true);
        
        $data = [
            'usuario_id' => $usuarioId,
            'datos' => $datos,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'ultima_actividad' => Carbon::now()->toIso8601String(),
            'activo' => true,
            'created_at' => Carbon::now()->toIso8601String(),
            'updated_at' => Carbon::now()->toIso8601String(),
        ];

        // TTL de 24 horas por defecto
        Redis::setex($this->prefix . $id, 86400, json_encode($data));

        return array_merge(['id' => $id], $data);
    }

    /**
     * Actualizar una sesión
     */
    public function update($id, $datos)
    {
        $existing = Redis::get($this->prefix . $id);
        
        if (!$existing) {
            return null;
        }

        $existingData = json_decode($existing, true);
        
        if (isset($existingData['activo']) && $existingData['activo'] === false) {
            return null;
        }

        foreach ($datos as $key => $value) {
            if ($key === 'datos') {
                $existingData['datos'] = array_merge($existingData['datos'] ?? [], $value);
            } elseif ($key !== 'id' && $key !== 'created_at') {
                $existingData[$key] = $value;
            }
        }

        $existingData['ultima_actividad'] = Carbon::now()->toIso8601String();
        $existingData['updated_at'] = Carbon::now()->toIso8601String();

        // Mantener TTL de 24 horas
        Redis::setex($this->prefix . $id, 86400, json_encode($existingData));

        return array_merge(['id' => $id], $existingData);
    }

    /**
     * Eliminar una sesión (borrado lógico)
     */
    public function delete($id)
    {
        $existing = Redis::get($this->prefix . $id);
        
        if (!$existing) {
            return false;
        }

        $existingData = json_decode($existing, true);
        $existingData['activo'] = false;
        $existingData['deleted_at'] = Carbon::now()->toIso8601String();

        Redis::set($this->prefix . $id, json_encode($existingData));

        return true;
    }

    /**
     * Restaurar una sesión eliminada
     */
    public function restore($id)
    {
        $existing = Redis::get($this->prefix . $id);
        
        if (!$existing) {
            return null;
        }

        $existingData = json_decode($existing, true);
        $existingData['activo'] = true;
        unset($existingData['deleted_at']);
        $existingData['updated_at'] = Carbon::now()->toIso8601String();

        Redis::setex($this->prefix . $id, 86400, json_encode($existingData));

        return array_merge(['id' => $id], $existingData);
    }

    /**
     * Obtener sesiones eliminadas
     */
    public function getDeleted()
    {
        $keys = Redis::keys($this->prefix . '*');
        $sesiones = [];

        foreach ($keys as $key) {
            $cleanKey = preg_replace('/^[^:]+:/', '', $key);
            $id = str_replace($this->prefix, '', $cleanKey);
            $data = Redis::get($this->prefix . $id);
            
            if ($data) {
                $decoded = json_decode($data, true);
                if (isset($decoded['activo']) && $decoded['activo'] === false) {
                    $sesiones[] = array_merge(['id' => $id], $decoded);
                }
            }
        }

        return $sesiones;
    }

    /**
     * Eliminar permanentemente una sesión
     */
    public function forceDelete($id)
    {
        return Redis::del($this->prefix . $id) > 0;
    }

    /**
     * Obtener sesiones por usuario
     */
    public function getByUsuario($usuarioId)
    {
        $all = $this->getAll();
        return array_filter($all, function($sesion) use ($usuarioId) {
            return isset($sesion['usuario_id']) && $sesion['usuario_id'] == $usuarioId;
        });
    }
}
