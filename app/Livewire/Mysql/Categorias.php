<?php

namespace App\Livewire\Mysql;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Categoria;
use App\Models\Log;

class Categorias extends Component
{
    use WithPagination;

    public $nombre = '';
    public $descripcion = '';
    public $editId = null;
    public $showModal = false;
    public $showDeletedModal = false;
    public $search = '';
    public $deletedItems = [];

    protected $rules = [
        'nombre' => 'required|min:2|max:255',
        'descripcion' => 'nullable|max:1000',
    ];

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->editId = $id;
        
        if ($id) {
            $categoria = Categoria::find($id);
            $this->nombre = $categoria->nombre;
            $this->descripcion = $categoria->descripcion;
        } else {
            $this->nombre = '';
            $this->descripcion = '';
        }
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save()
    {
        $this->validate();

        if ($this->editId) {
            $categoria = Categoria::find($this->editId);
            $categoria->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
            ]);
            
            Log::registrar('actualizar', 'Categoria', $categoria->id, "Categoría actualizada: {$this->nombre}");
            session()->flash('message', 'Categoría actualizada correctamente');
        } else {
            $categoria = Categoria::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'activo' => true,
            ]);
            
            Log::registrar('crear', 'Categoria', $categoria->id, "Categoría creada: {$this->nombre}");
            session()->flash('message', 'Categoría creada correctamente');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $categoria = Categoria::find($id);
        if ($categoria) {
            $categoria->activo = false;
            $categoria->save();
            $categoria->delete();
            
            Log::registrar('eliminar', 'Categoria', $id, "Categoría eliminada: {$categoria->nombre}");
            session()->flash('message', 'Categoría eliminada correctamente');
        }
    }

    public function showDeleted()
    {
        $this->deletedItems = Categoria::onlyTrashed()->get();
        $this->showDeletedModal = true;
    }

    public function restore($id)
    {
        $categoria = Categoria::onlyTrashed()->find($id);
        if ($categoria) {
            $categoria->restore();
            $categoria->activo = true;
            $categoria->save();
            
            Log::registrar('restaurar', 'Categoria', $id, "Categoría restaurada: {$categoria->nombre}");
            session()->flash('message', 'Categoría restaurada correctamente');
            $this->showDeleted();
        }
    }

    private function resetForm()
    {
        $this->nombre = '';
        $this->descripcion = '';
        $this->editId = null;
    }

    public function render()
    {
        $categorias = Categoria::where('activo', true)
            ->when($this->search, function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.mysql.categorias', [
            'categorias' => $categorias
        ])->layout('components.layouts.app');
    }
}
