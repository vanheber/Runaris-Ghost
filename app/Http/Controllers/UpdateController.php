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

            if ($this->updateService->isPackagedInstall()) {
                return redirect('/settings?tab=system')->with('success', __('Atualização baixada! Feche esta janela e abra o Runaris Ghost novamente para aplicar.'));
            }

            return redirect('/settings?tab=system')->with('success', __('Sistema atualizado com sucesso para a versão mais recente.'));
        } catch (\Exception $e) {
            return redirect('/settings?tab=system')->with('error', __('Falha na atualização:') . ' ' . $e->getMessage());
        }
    }

    /**
     * User opted to keep the current version: stop notifying about this version.
     */
    public function keepCurrent()
    {
        try {
            if ($this->updateService->keepCurrent()) {
                return redirect('/settings?tab=system')->with('success', __('Você optou por manter a versão atual. Não avisaremos mais sobre ela.'));
            }
            return redirect('/settings?tab=system')->with('success', __('Não há versão nova para pular no momento.'));
        } catch (\Exception $e) {
            return redirect('/settings?tab=system')->with('error', __('Falha:') . ' ' . $e->getMessage());
        }
    }

    /**
     * Rollback to previous state.
     */
    public function rollback()
    {
        try {
            $this->updateService->rollback();
            return redirect('/settings?tab=system')->with('success', __('Restauração concluída. O sistema voltou ao estado anterior à atualização.'));
        } catch (\Exception $e) {
            return redirect('/settings?tab=system')->with('error', __('Falha ao restaurar:') . ' ' . $e->getMessage());
        }
    }
}
