<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class SesionCacheService
{
    protected $prefix = 'sesion:';

    /**
     * Listar todas las sesiones activas
     */
    public function listarTodas()
    {
        $keys = Redis::keys($this->prefix . '*');
        $sesiones = [];

        foreach ($keys as $key) {
            $cleanKey = preg_replace('/^[^:]+:/', '', $key);
            $usuarioId = str_replace($this->prefix, '', $cleanKey);
            $data = Redis::get($this->prefix . $usuarioId);
            
            if ($data) {
                $decoded = json_decode($data, true);
                if (!isset($decoded['activo']) || $decoded['activo'] === true) {
                    $ttl = Redis::ttl($this->prefix . $usuarioId);
                    $sesiones[] = [
                        'usuario_id' => $usuarioId,
                        'datos' => $decoded,
                        'ttl' => $ttl,
                    ];
                }
            }
        }

        return $sesiones;
    }

    /**
     * Obtener una sesión por usuario ID
     */
    public function obtener($usuarioId)
    {
        $data = Redis::get($this->prefix . $usuarioId);
        
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
     * Obtener TTL de una sesión
     */
    public function obtenerTTL($usuarioId)
    {
        return Redis::ttl($this->prefix . $usuarioId);
    }

    /**
     * Crear una nueva sesión
     */
    public function crear($usuarioId, $datos = [])
    {
        $data = [
            'datos' => $datos,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'ultima_actividad' => Carbon::now()->toIso8601String(),
            'activo' => true,
            'created_at' => Carbon::now()->toIso8601String(),
            'updated_at' => Carbon::now()->toIso8601String(),
        ];

        // TTL de 24 horas por defecto
        Redis::setex($this->prefix . $usuarioId, 86400, json_encode($data));

        return $data;
    }

    /**
     * Actualizar una sesión
     */
    public function actualizar($usuarioId, $datos)
    {
        $existing = Redis::get($this->prefix . $usuarioId);
        
        if (!$existing) {
            return null;
        }

        $existingData = json_decode($existing, true);
        
        if (isset($existingData['activo']) && $existingData['activo'] === false) {
            return null;
        }

        $existingData['datos'] = $datos;
        $existingData['ultima_actividad'] = Carbon::now()->toIso8601String();
        $existingData['updated_at'] = Carbon::now()->toIso8601String();

        // Mantener TTL de 24 horas
        Redis::setex($this->prefix . $usuarioId, 86400, json_encode($existingData));

        return $existingData;
    }

    /**
     * Eliminar una sesión
     */
    public function eliminar($usuarioId)
    {
        return Redis::del($this->prefix . $usuarioId) > 0;
    }

    /**
     * Extender TTL de una sesión
     */
    public function extenderTTL($usuarioId, $segundos = 86400)
    {
        $existing = Redis::get($this->prefix . $usuarioId);
        
        if (!$existing) {
            return false;
        }

        $existingData = json_decode($existing, true);
        $existingData['ultima_actividad'] = Carbon::now()->toIso8601String();
        $existingData['updated_at'] = Carbon::now()->toIso8601String();

        Redis::setex($this->prefix . $usuarioId, $segundos, json_encode($existingData));

        return true;
    }

    /**
     * Obtener sesiones por usuario (legacy)
     */
    public function getByUsuario($usuarioId)
    {
        return $this->obtener($usuarioId);
    }
}
