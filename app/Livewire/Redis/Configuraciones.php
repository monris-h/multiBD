<?php

namespace App\Livewire\Redis;

use App\Services\ConfiguracionService;
use Livewire\Component;

class Configuraciones extends Component
{
    public $clave = '';
    public $valor = '';
    public $descripcion = '';
    public $tipo = 'string';
    public $editKey = null;
    public $showModal = false;
    public $showDeletedModal = false;
    public $search = '';
    public $deletedItems = [];

    protected $rules = [
        'clave' => 'required|min:2|max:255',
        'valor' => 'required',
        'descripcion' => 'nullable|max:500',
        'tipo' => 'required|in:string,integer,boolean,json',
    ];

    protected ConfiguracionService $configuracionService;

    public function boot(ConfiguracionService $configuracionService)
    {
        $this->configuracionService = $configuracionService;
    }

    public function openModal()
    {
        $this->reset(['clave', 'valor', 'descripcion', 'tipo', 'editKey']);
        $this->tipo = 'string';
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function edit($clave)
    {
        $config = $this->configuracionService->obtenerPorClave($clave);
        if ($config) {
            $this->editKey = $clave;
            $this->clave = $clave;
            $this->valor = is_array($config['valor']) ? json_encode($config['valor']) : $config['valor'];
            $this->descripcion = $config['descripcion'] ?? '';
            $this->tipo = $config['tipo'] ?? 'string';
            $this->showModal = true;
        }
    }

    public function save()
    {
        $this->validate();

        $valorFinal = $this->valor;
        if ($this->tipo === 'integer') {
            $valorFinal = (int) $this->valor;
        } elseif ($this->tipo === 'boolean') {
            $valorFinal = filter_var($this->valor, FILTER_VALIDATE_BOOLEAN);
        } elseif ($this->tipo === 'json') {
            $valorFinal = json_decode($this->valor, true) ?? $this->valor;
        }

        $this->configuracionService->crearOActualizar($this->clave, [
            'valor' => $valorFinal,
            'descripcion' => $this->descripcion,
            'tipo' => $this->tipo,
        ]);

        session()->flash('message', $this->editKey ? 'Configuración actualizada correctamente.' : 'Configuración creada correctamente.');
        $this->closeModal();
    }

    public function delete($clave)
    {
        $this->configuracionService->eliminar($clave);
        session()->flash('message', 'Configuración eliminada correctamente.');
    }

    public function openDeletedModal()
    {
        $todas = $this->configuracionService->listar();
        $this->deletedItems = array_filter($todas, fn($item) => isset($item['eliminado']) && $item['eliminado'] === true);
        $this->showDeletedModal = true;
    }

    public function closeDeletedModal()
    {
        $this->showDeletedModal = false;
    }

    public function restore($clave)
    {
        $this->configuracionService->restaurar($clave);
        $todas = $this->configuracionService->listar();
        $this->deletedItems = array_filter($todas, fn($item) => isset($item['eliminado']) && $item['eliminado'] === true);
        session()->flash('message', 'Configuración restaurada correctamente.');
    }

    public function render()
    {
        $todas = $this->configuracionService->listar();
        $configuraciones = array_filter($todas, fn($item) => !isset($item['eliminado']) || $item['eliminado'] !== true);

        if ($this->search) {
            $configuraciones = array_filter($configuraciones, function ($item, $key) {
                return stripos($key, $this->search) !== false ||
                       stripos($item['descripcion'] ?? '', $this->search) !== false;
            }, ARRAY_FILTER_USE_BOTH);
        }

        return view('livewire.redis.configuraciones', [
            'configuraciones' => $configuraciones,
        ]);
    }
}
