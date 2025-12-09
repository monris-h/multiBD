<?php

namespace App\Livewire\Mongodb;

use App\Models\Comentario;
use Livewire\Component;
use Livewire\WithPagination;

class Comentarios extends Component
{
    use WithPagination;

    public $entidad = '';
    public $entidad_id = '';
    public $usuario_nombre = '';
    public $contenido = '';
    public $calificacion = 5;
    public $editId = null;
    public $showModal = false;
    public $showDeletedModal = false;
    public $search = '';
    public $deletedItems = [];
    public $filterEntidad = '';

    protected $rules = [
        'entidad' => 'required|in:producto,categoria,cliente,orden',
        'entidad_id' => 'required',
        'usuario_nombre' => 'required|min:2|max:255',
        'contenido' => 'required|min:5|max:1000',
        'calificacion' => 'required|integer|min:1|max:5',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->reset(['entidad', 'entidad_id', 'usuario_nombre', 'contenido', 'calificacion', 'editId']);
        $this->calificacion = 5;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function edit($id)
    {
        $comentario = Comentario::find($id);
        $this->editId = $id;
        $this->entidad = $comentario->entidad;
        $this->entidad_id = $comentario->entidad_id;
        $this->usuario_nombre = $comentario->usuario_nombre;
        $this->contenido = $comentario->contenido;
        $this->calificacion = $comentario->calificacion;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editId) {
            $comentario = Comentario::find($this->editId);
            $comentario->update([
                'entidad' => $this->entidad,
                'entidad_id' => $this->entidad_id,
                'usuario_nombre' => $this->usuario_nombre,
                'contenido' => $this->contenido,
                'calificacion' => (int) $this->calificacion,
            ]);
            session()->flash('message', 'Comentario actualizado correctamente.');
        } else {
            Comentario::create([
                'entidad' => $this->entidad,
                'entidad_id' => $this->entidad_id,
                'usuario_nombre' => $this->usuario_nombre,
                'contenido' => $this->contenido,
                'calificacion' => (int) $this->calificacion,
                'activo' => true,
            ]);
            session()->flash('message', 'Comentario creado correctamente.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $comentario = Comentario::find($id);
        $comentario->activo = false;
        $comentario->save();
        session()->flash('message', 'Comentario eliminado correctamente.');
    }

    public function openDeletedModal()
    {
        $this->deletedItems = Comentario::where('activo', false)->get();
        $this->showDeletedModal = true;
    }

    public function closeDeletedModal()
    {
        $this->showDeletedModal = false;
    }

    public function restore($id)
    {
        $comentario = Comentario::find($id);
        $comentario->activo = true;
        $comentario->save();
        $this->deletedItems = Comentario::where('activo', false)->get();
        session()->flash('message', 'Comentario restaurado correctamente.');
    }

    public function forceDelete($id)
    {
        Comentario::destroy($id);
        $this->deletedItems = Comentario::where('activo', false)->get();
        session()->flash('message', 'Comentario eliminado permanentemente.');
    }

    public function render()
    {
        $query = Comentario::where('activo', '!=', false);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('contenido', 'like', '%' . $this->search . '%')
                  ->orWhere('usuario_nombre', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterEntidad) {
            $query->where('entidad', $this->filterEntidad);
        }

        $comentarios = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.mongodb.comentarios', [
            'comentarios' => $comentarios,
        ]);
    }
}
