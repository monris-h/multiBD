<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Log extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'logs';

    protected $fillable = [
        'accion',
        'entidad',
        'entidad_id',
        'usuario_id',
        'datos_anteriores',
        'datos_nuevos',
        'ip_address',
        'user_agent',
        'activo',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Tipos de acciones disponibles
     */
    const ACCIONES = [
        'crear' => 'Crear',
        'actualizar' => 'Actualizar',
        'eliminar' => 'Eliminar',
        'login' => 'Inicio de Sesión',
        'logout' => 'Cierre de Sesión',
    ];

    /**
     * Scope para logs activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por entidad
     */
    public function scopePorEntidad($query, $entidad)
    {
        return $query->where('entidad', $entidad);
    }

    /**
     * Scope para filtrar por acción
     */
    public function scopePorAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    /**
     * Crear un log de actividad
     */
    public static function registrar($accion, $entidad, $entidadId = null, $datosAnteriores = null, $datosNuevos = null)
    {
        return self::create([
            'accion' => $accion,
            'entidad' => $entidad,
            'entidad_id' => $entidadId,
            'usuario_id' => auth()->id(),
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $datosNuevos,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'activo' => true,
        ]);
    }
}
