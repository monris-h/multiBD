<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class ConfiguracionService
{
    protected $prefix = 'config:';

    /**
     * Listar todas las configuraciones activas
     */
    public function listar()
    {
        $keys = Redis::keys($this->prefix . '*');
        $configuraciones = [];

        foreach ($keys as $key) {
            $cleanKey = preg_replace('/^[^:]+:/', '', $key);
            $nombre = str_replace($this->prefix, '', $cleanKey);
            $data = Redis::get($this->prefix . $nombre);
            
            if ($data) {
                $decoded = json_decode($data, true);
                if (!isset($decoded['activo']) || $decoded['activo'] === true) {
                    $configuraciones[] = array_merge(['clave' => $nombre], $decoded);
                }
            }
        }

        return $configuraciones;
    }

    /**
     * Obtener una configuración por clave
     */
    public function obtener($clave)
    {
        $data = Redis::get($this->prefix . $clave);
        
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
     * Crear una configuración
     */
    public function crear($clave, $datos)
    {
        $data = [
            'valor' => $datos['valor'] ?? '',
            'descripcion' => $datos['descripcion'] ?? null,
            'activo' => true,
            'created_at' => Carbon::now()->toIso8601String(),
            'updated_at' => Carbon::now()->toIso8601String(),
        ];

        Redis::set($this->prefix . $clave, json_encode($data));

        return array_merge(['clave' => $clave], $data);
    }

    /**
     * Actualizar una configuración existente
     */
    public function actualizar($clave, $datos)
    {
        $existing = Redis::get($this->prefix . $clave);
        
        if (!$existing) {
            return null;
        }

        $existingData = json_decode($existing, true);
        
        if (isset($existingData['activo']) && $existingData['activo'] === false) {
            return null;
        }

        foreach ($datos as $key => $value) {
            if ($key !== 'clave' && $key !== 'created_at') {
                $existingData[$key] = $value;
            }
        }

        $existingData['updated_at'] = Carbon::now()->toIso8601String();

        Redis::set($this->prefix . $clave, json_encode($existingData));

        return array_merge(['clave' => $clave], $existingData);
    }

    /**
     * Eliminar una configuración (borrado lógico)
     */
    public function eliminar($clave)
    {
        $existing = Redis::get($this->prefix . $clave);
        
        if (!$existing) {
            return false;
        }

        $existingData = json_decode($existing, true);
        $existingData['activo'] = false;
        $existingData['deleted_at'] = Carbon::now()->toIso8601String();

        Redis::set($this->prefix . $clave, json_encode($existingData));

        return true;
    }

    /**
     * Restaurar una configuración eliminada
     */
    public function restaurar($clave)
    {
        $existing = Redis::get($this->prefix . $clave);
        
        if (!$existing) {
            return null;
        }

        $existingData = json_decode($existing, true);
        $existingData['activo'] = true;
        unset($existingData['deleted_at']);
        $existingData['updated_at'] = Carbon::now()->toIso8601String();

        Redis::set($this->prefix . $clave, json_encode($existingData));

        return array_merge(['clave' => $clave], $existingData);
    }

    /**
     * Listar configuraciones eliminadas
     */
    public function listarEliminadas()
    {
        $keys = Redis::keys($this->prefix . '*');
        $configuraciones = [];

        foreach ($keys as $key) {
            $cleanKey = preg_replace('/^[^:]+:/', '', $key);
            $nombre = str_replace($this->prefix, '', $cleanKey);
            $data = Redis::get($this->prefix . $nombre);
            
            if ($data) {
                $decoded = json_decode($data, true);
                if (isset($decoded['activo']) && $decoded['activo'] === false) {
                    $configuraciones[] = array_merge(['clave' => $nombre], $decoded);
                }
            }
        }

        return $configuraciones;
    }
}
