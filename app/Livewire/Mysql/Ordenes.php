<?php

namespace App\Livewire\Mysql;

use App\Models\Orden;
use App\Models\Cliente;
use App\Models\Producto;
use Livewire\Component;
use Livewire\WithPagination;

class Ordenes extends Component
{
    use WithPagination;

    public $cliente_id = '';
    public $productos_seleccionados = [];
    public $cantidades = [];
    public $estado = 'pendiente';
    public $notas = '';
    public $editId = null;
    public $showModal = false;
    public $showDeletedModal = false;
    public $showDetailModal = false;
    public $search = '';
    public $deletedItems = [];
    public $clientes = [];
    public $productosDisponibles = [];
    public $ordenDetalle = null;

    protected $rules = [
        'cliente_id' => 'required|exists:clientes,id',
        'estado' => 'required|in:pendiente,procesando,completada,cancelada',
        'notas' => 'nullable|max:1000',
    ];

    public function mount()
    {
        $this->loadClientes();
        $this->loadProductos();
    }

    public function loadClientes()
    {
        $this->clientes = Cliente::orderBy('nombre')->get();
    }

    public function loadProductos()
    {
        $this->productosDisponibles = Producto::where('stock', '>', 0)->orderBy('nombre')->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->reset(['cliente_id', 'productos_seleccionados', 'cantidades', 'estado', 'notas', 'editId']);
        $this->estado = 'pendiente';
        $this->loadClientes();
        $this->loadProductos();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function toggleProducto($productoId)
    {
        if (in_array($productoId, $this->productos_seleccionados)) {
            $this->productos_seleccionados = array_diff($this->productos_seleccionados, [$productoId]);
            unset($this->cantidades[$productoId]);
        } else {
            $this->productos_seleccionados[] = $productoId;
            $this->cantidades[$productoId] = 1;
        }
    }

    public function edit($id)
    {
        $orden = Orden::with('productos')->findOrFail($id);
        $this->editId = $id;
        $this->cliente_id = $orden->cliente_id;
        $this->estado = $orden->estado;
        $this->notas = $orden->notas;
        $this->productos_seleccionados = $orden->productos->pluck('id')->toArray();
        $this->cantidades = [];
        foreach ($orden->productos as $producto) {
            $this->cantidades[$producto->id] = $producto->pivot->cantidad;
        }
        $this->loadClientes();
        $this->loadProductos();
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if (empty($this->productos_seleccionados)) {
            session()->flash('error', 'Debe seleccionar al menos un producto.');
            return;
        }

        $total = 0;
        $productosData = [];
        foreach ($this->productos_seleccionados as $productoId) {
            $producto = Producto::find($productoId);
            $cantidad = $this->cantidades[$productoId] ?? 1;
            $subtotalProducto = $producto->precio * $cantidad;
            $total += $subtotalProducto;
            $productosData[$productoId] = [
                'cantidad' => $cantidad,
                'precio_unitario' => $producto->precio,
            ];
        }

        if ($this->editId) {
            $orden = Orden::findOrFail($this->editId);
            $orden->update([
                'cliente_id' => $this->cliente_id,
                'estado' => $this->estado,
                'subtotal' => $total,
                'total' => $total,
                'notas' => $this->notas,
            ]);
            $orden->productos()->sync($productosData);
            session()->flash('message', 'Orden actualizada correctamente.');
        } else {
            $orden = Orden::create([
                'numero_orden' => 'ORD-' . strtoupper(uniqid()),
                'cliente_id' => $this->cliente_id,
                'estado' => $this->estado,
                'subtotal' => $total,
                'total' => $total,
                'notas' => $this->notas,
            ]);
            $orden->productos()->attach($productosData);
            session()->flash('message', 'Orden creada correctamente.');
        }

        $this->closeModal();
    }

    public function showDetail($id)
    {
        $this->ordenDetalle = Orden::with(['cliente', 'productos'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->ordenDetalle = null;
    }

    public function updateEstado($id, $estado)
    {
        $orden = Orden::findOrFail($id);
        $orden->update(['estado' => $estado]);
        session()->flash('message', 'Estado actualizado correctamente.');
    }

    public function delete($id)
    {
        Orden::findOrFail($id)->delete();
        session()->flash('message', 'Orden eliminada correctamente.');
    }

    public function openDeletedModal()
    {
        $this->deletedItems = Orden::onlyTrashed()->with('cliente')->get();
        $this->showDeletedModal = true;
    }

    public function closeDeletedModal()
    {
        $this->showDeletedModal = false;
    }

    public function restore($id)
    {
        Orden::withTrashed()->findOrFail($id)->restore();
        $this->deletedItems = Orden::onlyTrashed()->with('cliente')->get();
        session()->flash('message', 'Orden restaurada correctamente.');
    }

    public function forceDelete($id)
    {
        Orden::withTrashed()->findOrFail($id)->forceDelete();
        $this->deletedItems = Orden::onlyTrashed()->with('cliente')->get();
        session()->flash('message', 'Orden eliminada permanentemente.');
    }

    public function render()
    {
        $ordenes = Orden::with(['cliente', 'productos'])
            ->when($this->search, function ($query) {
                $query->whereHas('cliente', function ($q) {
                    $q->where('nombre', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.mysql.ordenes', [
            'ordenes' => $ordenes,
        ]);
    }
}
