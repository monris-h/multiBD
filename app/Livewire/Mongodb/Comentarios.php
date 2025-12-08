<?php

namespace App\Livewire\Mongodb;

use App\Models\Comentario;
use Livewire\Component;
use Livewire\WithPagination;

class Comentarios extends Component
{
    use WithPagination;

    public $entidad_tipo = '';
    public $entidad_id = '';
    public $usuario = '';
    public $contenido = '';
    public $calificacion = 5;
    public $editId = null;
    public $showModal = false;
    public $showDeletedModal = false;
    public $search = '';
    public $deletedItems = [];
    public $filterEntidad = '';

    protected $rules = [
        'entidad_tipo' => 'required|in:producto,categoria,cliente,orden',
        'entidad_id' => 'required',
        'usuario' => 'required|min:2|max:255',
        'contenido' => 'required|min:5|max:1000',
        'calificacion' => 'required|integer|min:1|max:5',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->reset(['entidad_tipo', 'entidad_id', 'usuario', 'contenido', 'calificacion', 'editId']);
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
        $this->entidad_tipo = $comentario->entidad_tipo;
        $this->entidad_id = $comentario->entidad_id;
        $this->usuario = $comentario->usuario;
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
                'entidad_tipo' => $this->entidad_tipo,
                'entidad_id' => $this->entidad_id,
                'usuario' => $this->usuario,
                'contenido' => $this->contenido,
                'calificacion' => (int) $this->calificacion,
            ]);
            session()->flash('message', 'Comentario actualizado correctamente.');
        } else {
            Comentario::create([
                'entidad_tipo' => $this->entidad_tipo,
                'entidad_id' => $this->entidad_id,
                'usuario' => $this->usuario,
                'contenido' => $this->contenido,
                'calificacion' => (int) $this->calificacion,
            ]);
            session()->flash('message', 'Comentario creado correctamente.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $comentario = Comentario::find($id);
        $comentario->eliminado = true;
        $comentario->deleted_at = now();
        $comentario->save();
        session()->flash('message', 'Comentario eliminado correctamente.');
    }

    public function openDeletedModal()
    {
        $this->deletedItems = Comentario::where('eliminado', true)->get();
        $this->showDeletedModal = true;
    }

    public function closeDeletedModal()
    {
        $this->showDeletedModal = false;
    }

    public function restore($id)
    {
        $comentario = Comentario::find($id);
        $comentario->eliminado = false;
        $comentario->deleted_at = null;
        $comentario->save();
        $this->deletedItems = Comentario::where('eliminado', true)->get();
        session()->flash('message', 'Comentario restaurado correctamente.');
    }

    public function forceDelete($id)
    {
        Comentario::destroy($id);
        $this->deletedItems = Comentario::where('eliminado', true)->get();
        session()->flash('message', 'Comentario eliminado permanentemente.');
    }

    public function render()
    {
        $query = Comentario::where('eliminado', '!=', true);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('contenido', 'like', '%' . $this->search . '%')
                  ->orWhere('usuario', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterEntidad) {
            $query->where('entidad_tipo', $this->filterEntidad);
        }

        $comentarios = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.mongodb.comentarios', [
            'comentarios' => $comentarios,
        ]);
    }
}
