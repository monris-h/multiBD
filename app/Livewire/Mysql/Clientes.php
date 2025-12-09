<?php

namespace App\Livewire\Mysql;

use App\Models\Cliente;
use Livewire\Component;
use Livewire\WithPagination;

class Clientes extends Component
{
    use WithPagination;

    public $nombre = '';
    public $apellido = '';
    public $email = '';
    public $telefono = '';
    public $direccion = '';
    public $editId = null;
    public $showModal = false;
    public $showDeletedModal = false;
    public $search = '';
    public $deletedItems = [];

    protected $rules = [
        'nombre' => 'required|min:2|max:255',
        'apellido' => 'required|min:2|max:255',
        'email' => 'required|email|max:255',
        'telefono' => 'nullable|max:20',
        'direccion' => 'nullable|max:500',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->reset(['nombre', 'apellido', 'email', 'telefono', 'direccion', 'editId']);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function edit($id)
    {
        $cliente = Cliente::findOrFail($id);
        $this->editId = $id;
        $this->nombre = $cliente->nombre;
        $this->apellido = $cliente->apellido;
        $this->email = $cliente->email;
        $this->telefono = $cliente->telefono;
        $this->direccion = $cliente->direccion;
        $this->showModal = true;
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->editId) {
            $rules['email'] = 'required|email|max:255|unique:clientes,email,' . $this->editId;
        } else {
            $rules['email'] = 'required|email|max:255|unique:clientes,email';
        }
        $this->validate($rules);

        if ($this->editId) {
            $cliente = Cliente::findOrFail($this->editId);
            $cliente->update([
                'nombre' => $this->nombre,
                'apellido' => $this->apellido,
                'email' => $this->email,
                'telefono' => $this->telefono,
                'direccion' => $this->direccion,
            ]);
            session()->flash('message', 'Cliente actualizado correctamente.');
        } else {
            Cliente::create([
                'nombre' => $this->nombre,
                'apellido' => $this->apellido,
                'email' => $this->email,
                'telefono' => $this->telefono,
                'direccion' => $this->direccion,
            ]);
            session()->flash('message', 'Cliente creado correctamente.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        Cliente::findOrFail($id)->delete();
        session()->flash('message', 'Cliente eliminado correctamente.');
    }

    public function openDeletedModal()
    {
        $this->deletedItems = Cliente::onlyTrashed()->get();
        $this->showDeletedModal = true;
    }

    public function closeDeletedModal()
    {
        $this->showDeletedModal = false;
    }

    public function restore($id)
    {
        Cliente::withTrashed()->findOrFail($id)->restore();
        $this->deletedItems = Cliente::onlyTrashed()->get();
        session()->flash('message', 'Cliente restaurado correctamente.');
    }

    public function forceDelete($id)
    {
        Cliente::withTrashed()->findOrFail($id)->forceDelete();
        $this->deletedItems = Cliente::onlyTrashed()->get();
        session()->flash('message', 'Cliente eliminado permanentemente.');
    }

    public function render()
    {
        $clientes = Cliente::when($this->search, function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.mysql.clientes', [
            'clientes' => $clientes,
        ]);
    }
}
