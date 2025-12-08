<?php

namespace App\Livewire\Redis;

use App\Services\SesionCacheService;
use Livewire\Component;

class Sesiones extends Component
{
    public $usuario_id = '';
    public $datos = '';
    public $ip_address = '';
    public $user_agent = '';
    public $editId = null;
    public $showModal = false;
    public $showDeletedModal = false;
    public $search = '';
    public $deletedItems = [];

    protected $rules = [
        'usuario_id' => 'required',
        'datos' => 'nullable',
        'ip_address' => 'nullable|max:45',
        'user_agent' => 'nullable|max:255',
    ];

    protected SesionCacheService $sesionService;

    public function boot(SesionCacheService $sesionService)
    {
        $this->sesionService = $sesionService;
    }

    public function openModal()
    {
        $this->reset(['usuario_id', 'datos', 'ip_address', 'user_agent', 'editId']);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function edit($id)
    {
        $sesion = $this->sesionService->obtener($id);
        if ($sesion) {
            $this->editId = $id;
            $this->usuario_id = $sesion['usuario_id'] ?? '';
            $this->datos = is_array($sesion['datos'] ?? null) ? json_encode($sesion['datos']) : ($sesion['datos'] ?? '');
            $this->ip_address = $sesion['ip_address'] ?? '';
            $this->user_agent = $sesion['user_agent'] ?? '';
            $this->showModal = true;
        }
    }

    public function save()
    {
        $this->validate();

        $datosArray = json_decode($this->datos, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $datosArray = $this->datos;
        }

        $data = [
            'usuario_id' => $this->usuario_id,
            'datos' => $datosArray,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
        ];

        if ($this->editId) {
            $this->sesionService->actualizar($this->editId, $data);
            session()->flash('message', 'Sesión actualizada correctamente.');
        } else {
            $this->sesionService->crear($data);
            session()->flash('message', 'Sesión creada correctamente.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $this->sesionService->eliminar($id);
        session()->flash('message', 'Sesión eliminada correctamente.');
    }

    public function openDeletedModal()
    {
        $todas = $this->sesionService->listarTodas();
        $this->deletedItems = array_filter($todas, fn($item) => isset($item['eliminado']) && $item['eliminado'] === true);
        $this->showDeletedModal = true;
    }

    public function closeDeletedModal()
    {
        $this->showDeletedModal = false;
    }

    public function restore($id)
    {
        $this->sesionService->restaurar($id);
        $todas = $this->sesionService->listarTodas();
        $this->deletedItems = array_filter($todas, fn($item) => isset($item['eliminado']) && $item['eliminado'] === true);
        session()->flash('message', 'Sesión restaurada correctamente.');
    }

    public function getTTL($id)
    {
        return $this->sesionService->obtenerTTL($id);
    }

    public function render()
    {
        $todas = $this->sesionService->listarTodas();
        $sesiones = array_filter($todas, fn($item) => !isset($item['eliminado']) || $item['eliminado'] !== true);

        if ($this->search) {
            $sesiones = array_filter($sesiones, function ($item) {
                return stripos($item['usuario_id'] ?? '', $this->search) !== false ||
                       stripos($item['ip_address'] ?? '', $this->search) !== false;
            });
        }

        // Añadir TTL a cada sesión
        foreach ($sesiones as $id => &$sesion) {
            $sesion['ttl'] = $this->getTTL($id);
        }

        return view('livewire.redis.sesiones', [
            'sesiones' => $sesiones,
        ]);
    }
}
