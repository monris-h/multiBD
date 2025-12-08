<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Orden;
use App\Models\Log;
use App\Models\Comentario;
use App\Services\ConfiguracionService;
use App\Services\SesionCacheService;

class Dashboard extends Component
{
    public $mysqlStats = [];
    public $mongoStats = [];
    public $redisStats = [];
    public $recentLogs = [];

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        // MySQL Stats
        try {
            $this->mysqlStats = [
                'categorias' => Categoria::count(),
                'productos' => Producto::count(),
                'clientes' => Cliente::count(),
                'ordenes' => Orden::count(),
            ];
        } catch (\Exception $e) {
            $this->mysqlStats = ['categorias' => 0, 'productos' => 0, 'clientes' => 0, 'ordenes' => 0];
        }

        // MongoDB Stats
        try {
            $this->mongoStats = [
                'logs' => Log::count(),
                'comentarios' => Comentario::where('eliminado', '!=', true)->count(),
                'calificacion_promedio' => Comentario::where('eliminado', '!=', true)->avg('calificacion') ?? 0,
            ];
        } catch (\Exception $e) {
            $this->mongoStats = ['logs' => 0, 'comentarios' => 0, 'calificacion_promedio' => 0];
        }

        // Redis Stats
        try {
            $configService = new ConfiguracionService();
            $sesionService = new SesionCacheService();
            $this->redisStats = [
                'configuraciones' => count($configService->listar()),
                'sesiones' => count($sesionService->listarTodas()),
            ];
        } catch (\Exception $e) {
            $this->redisStats = ['configuraciones' => 0, 'sesiones' => 0];
        }

        // Últimos logs
        try {
            $this->recentLogs = Log::orderBy('created_at', 'desc')->take(5)->get();
        } catch (\Exception $e) {
            $this->recentLogs = collect([]);
        }
    }

    public function render()
    {
        return view('livewire.dashboard')->layout('components.layouts.app');
    }
}
