<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orden extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ordenes';

    protected $fillable = [
        'numero_orden',
        'cliente_id',
        'subtotal',
        'impuestos',
        'total',
        'estado',
        'notas',
        'fecha_entrega',
        'activo',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha_entrega' => 'datetime',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Estados disponibles para la orden
     */
    const ESTADOS = [
        'pendiente' => 'Pendiente',
        'procesando' => 'Procesando',
        'enviado' => 'Enviado',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado',
    ];

    /**
     * Relación con cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Relación con productos (muchos a muchos)
     */
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'orden_producto')
            ->withPivot('cantidad', 'precio_unitario')
            ->withTimestamps();
    }

    /**
     * Scope para órdenes activas
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para órdenes por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Generar número de orden único
     */
    public static function generarNumeroOrden()
    {
        $prefijo = 'ORD';
        $fecha = now()->format('Ymd');
        $aleatorio = strtoupper(substr(uniqid(), -4));
        return "{$prefijo}-{$fecha}-{$aleatorio}";
    }
}
