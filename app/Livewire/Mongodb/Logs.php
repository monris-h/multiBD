<?php

namespace App\Livewire\Mongodb;

use App\Models\Log;
use Livewire\Component;
use Livewire\WithPagination;

class Logs extends Component
{
    use WithPagination;

    public $search = '';
    public $filterAccion = '';
    public $filterEntidad = '';
    public $showDetailModal = false;
    public $logDetalle = null;

    protected $queryString = ['search', 'filterAccion', 'filterEntidad'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterAccion()
    {
        $this->resetPage();
    }

    public function updatingFilterEntidad()
    {
        $this->resetPage();
    }

    public function showDetail($id)
    {
        $this->logDetalle = Log::find($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->logDetalle = null;
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterAccion', 'filterEntidad']);
    }

    public function render()
    {
        $query = Log::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('descripcion', 'like', '%' . $this->search . '%')
                  ->orWhere('usuario', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterAccion) {
            $query->where('accion', $this->filterAccion);
        }

        if ($this->filterEntidad) {
            $query->where('entidad', $this->filterEntidad);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(15);

        $acciones = Log::distinct('accion')->pluck('accion')->filter()->values();
        $entidades = Log::distinct('entidad')->pluck('entidad')->filter()->values();

        return view('livewire.mongodb.logs', [
            'logs' => $logs,
            'acciones' => $acciones,
            'entidades' => $entidades,
        ]);
    }
}
