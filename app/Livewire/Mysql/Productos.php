<?php

namespace App\Livewire\Mysql;

use App\Models\Producto;
use App\Models\Categoria;
use Livewire\Component;
use Livewire\WithPagination;

class Productos extends Component
{
    use WithPagination;

    public $nombre = '';
    public $descripcion = '';
    public $precio = '';
    public $stock = '';
    public $categoria_id = '';
    public $editId = null;
    public $showModal = false;
    public $showDeletedModal = false;
    public $search = '';
    public $deletedItems = [];
    public $categorias = [];

    protected $rules = [
        'nombre' => 'required|min:2|max:255',
        'descripcion' => 'nullable|max:1000',
        'precio' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'categoria_id' => 'required|exists:categorias,id',
    ];

    public function mount()
    {
        $this->loadCategorias();
    }

    public function loadCategorias()
    {
        $this->categorias = Categoria::orderBy('nombre')->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->reset(['nombre', 'descripcion', 'precio', 'stock', 'categoria_id', 'editId']);
        $this->loadCategorias();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function edit($id)
    {
        $producto = Producto::findOrFail($id);
        $this->editId = $id;
        $this->nombre = $producto->nombre;
        $this->descripcion = $producto->descripcion;
        $this->precio = $producto->precio;
        $this->stock = $producto->stock;
        $this->categoria_id = $producto->categoria_id;
        $this->loadCategorias();
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editId) {
            $producto = Producto::findOrFail($this->editId);
            $producto->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'precio' => $this->precio,
                'stock' => $this->stock,
                'categoria_id' => $this->categoria_id,
            ]);
            session()->flash('message', 'Producto actualizado correctamente.');
        } else {
            Producto::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'precio' => $this->precio,
                'stock' => $this->stock,
                'categoria_id' => $this->categoria_id,
            ]);
            session()->flash('message', 'Producto creado correctamente.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        Producto::findOrFail($id)->delete();
        session()->flash('message', 'Producto eliminado correctamente.');
    }

    public function openDeletedModal()
    {
        $this->deletedItems = Producto::onlyTrashed()->with('categoria')->get();
        $this->showDeletedModal = true;
    }

    public function closeDeletedModal()
    {
        $this->showDeletedModal = false;
    }

    public function restore($id)
    {
        Producto::withTrashed()->findOrFail($id)->restore();
        $this->deletedItems = Producto::onlyTrashed()->with('categoria')->get();
        session()->flash('message', 'Producto restaurado correctamente.');
    }

    public function forceDelete($id)
    {
        Producto::withTrashed()->findOrFail($id)->forceDelete();
        $this->deletedItems = Producto::onlyTrashed()->with('categoria')->get();
        session()->flash('message', 'Producto eliminado permanentemente.');
    }

    public function render()
    {
        $productos = Producto::with('categoria')
            ->when($this->search, function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.mysql.productos', [
            'productos' => $productos,
        ]);
    }
}
