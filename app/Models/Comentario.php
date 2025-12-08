<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Comentario extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'comentarios';

    protected $fillable = [
        'contenido',
        'entidad',
        'entidad_id',
        'usuario_id',
        'usuario_nombre',
        'calificacion',
        'metadata',
        'activo',
    ];

    protected $casts = [
        'calificacion' => 'integer',
        'metadata' => 'array',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Scope para comentarios activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por entidad
     */
    public function scopePorEntidad($query, $entidad, $entidadId = null)
    {
        $query->where('entidad', $entidad);
        
        if ($entidadId) {
            $query->where('entidad_id', $entidadId);
        }
        
        return $query;
    }

    /**
     * Scope para filtrar por calificación mínima
     */
    public function scopeCalificacionMinima($query, $minimo)
    {
        return $query->where('calificacion', '>=', $minimo);
    }

    /**
     * Obtener promedio de calificaciones para una entidad
     */
    public static function promedioCalificacion($entidad, $entidadId)
    {
        return self::activos()
            ->porEntidad($entidad, $entidadId)
            ->whereNotNull('calificacion')
            ->avg('calificacion');
    }
}
