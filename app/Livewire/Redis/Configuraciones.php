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

        $datos = [
            'valor' => $valorFinal,
            'descripcion' => $this->descripcion,
            'tipo' => $this->tipo,
        ];

        if ($this->editKey) {
            $this->configuracionService->actualizar($this->clave, $datos);
            session()->flash('message', 'Configuración actualizada correctamente.');
        } else {
            $this->configuracionService->crear($this->clave, $datos);
            session()->flash('message', 'Configuración creada correctamente.');
        }

        $this->closeModal();
    }

    public function delete($clave)
    {
        $this->configuracionService->eliminar($clave);
        session()->flash('message', 'Configuración eliminada correctamente.');
    }

    public function openDeletedModal()
    {
        $lista = $this->configuracionService->listarEliminadas();
        $this->deletedItems = [];
        foreach ($lista as $item) {
            $clave = $item['clave'];
            unset($item['clave']);
            $this->deletedItems[$clave] = $item;
        }
        $this->showDeletedModal = true;
    }

    public function closeDeletedModal()
    {
        $this->showDeletedModal = false;
    }

    public function restore($clave)
    {
        $this->configuracionService->restaurar($clave);
        $lista = $this->configuracionService->listarEliminadas();
        $this->deletedItems = [];
        foreach ($lista as $item) {
            $claveItem = $item['clave'];
            unset($item['clave']);
            $this->deletedItems[$claveItem] = $item;
        }
        session()->flash('message', 'Configuración restaurada correctamente.');
    }

    public function render()
    {
        $lista = $this->configuracionService->listar();
        
        // Transformar array indexado a asociativo usando 'clave' como key
        $configuraciones = [];
        foreach ($lista as $item) {
            $clave = $item['clave'];
            unset($item['clave']);
            $configuraciones[$clave] = $item;
        }

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
