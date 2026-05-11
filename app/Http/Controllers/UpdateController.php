<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UpdateService;

class UpdateController extends Controller
{
    protected $updateService;

    public function __construct(UpdateService $updateService)
    {
        $this->updateService = $updateService;
    }

    /**
     * Check for available updates.
     */
    public function check()
    {
        $result = $this->updateService->checkUpdate();
        return response()->json($result);
    }

    /**
     * Start the update process.
     */
    public function update(Request $request)
    {
        try {
            $this->updateService->runUpdate();
            return redirect('/settings?tab=system')->with('success', 'Sistema atualizado com sucesso para a versão mais recente.');
        } catch (\Exception $e) {
            return redirect('/settings?tab=system')->with('error', 'Falha na atualização: ' . $e->getMessage());
        }
    }

    /**
     * Rollback to previous state.
     */
    public function rollback()
    {
        try {
            $this->updateService->rollback();
            return redirect('/settings?tab=system')->with('success', 'Restauração concluída. O sistema voltou ao estado anterior à atualização.');
        } catch (\Exception $e) {
            return redirect('/settings?tab=system')->with('error', 'Falha ao restaurar: ' . $e->getMessage());
        }
    }
}
