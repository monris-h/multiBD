<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class ConfiguracionService
{
    protected $prefix = 'config:';
    protected $deletedPrefix = 'config_deleted:';

    /**
     * Obtener todas las configuraciones activas
     */
    public function getAll()
    {
        $keys = Redis::keys($this->prefix . '*');
        $configuraciones = [];

        foreach ($keys as $key) {
            // Remover el prefijo de la base de datos de Redis
            $cleanKey = preg_replace('/^[^:]+:/', '', $key);
            $nombre = str_replace($this->prefix, '', $cleanKey);
            $data = $this->get($nombre);
            if ($data && (!isset($data['activo']) || $data['activo'] === true)) {
                $configuraciones[] = array_merge(['clave' => $nombre], $data);
            }
        }

        return $configuraciones;
    }

    /**
     * Obtener una configuración por clave
     */
    public function get($clave)
    {
        $data = Redis::get($this->prefix . $clave);
        
        if (!$data) {
            return null;
        }

        $decoded = json_decode($data, true);
        
        // Verificar si está marcada como eliminada (borrado lógico)
        if (isset($decoded['activo']) && $decoded['activo'] === false) {
            return null;
        }

        return $decoded;
    }

    /**
     * Crear o actualizar una configuración
     */
    public function set($clave, $valor, $descripcion = null, $tipo = 'string')
    {
        $data = [
            'valor' => $valor,
            'descripcion' => $descripcion,
            'tipo' => $tipo,
            'activo' => true,
            'created_at' => Carbon::now()->toIso8601String(),
            'updated_at' => Carbon::now()->toIso8601String(),
        ];

        // Si ya existe, mantener created_at original
        $existing = Redis::get($this->prefix . $clave);
        if ($existing) {
            $existingData = json_decode($existing, true);
            if (isset($existingData['created_at'])) {
                $data['created_at'] = $existingData['created_at'];
            }
        }

        Redis::set($this->prefix . $clave, json_encode($data));

        return array_merge(['clave' => $clave], $data);
    }

    /**
     * Actualizar una configuración existente
     */
    public function update($clave, $datos)
    {
        $existing = Redis::get($this->prefix . $clave);
        
        if (!$existing) {
            return null;
        }

        $existingData = json_decode($existing, true);
        
        // Verificar si está eliminada
        if (isset($existingData['activo']) && $existingData['activo'] === false) {
            return null;
        }

        // Actualizar solo los campos proporcionados
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
    public function delete($clave)
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
    public function restore($clave)
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
     * Obtener todas las configuraciones eliminadas
     */
    public function getDeleted()
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

    /**
     * Eliminar permanentemente una configuración
     */
    public function forceDelete($clave)
    {
        return Redis::del($this->prefix . $clave) > 0;
    }
}
