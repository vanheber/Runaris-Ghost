<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display the global system settings or documentation.
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'general');
        $docContent = null;

        if ($tab === 'docs') {
            $docPath = base_path('docs/USER_MANUAL.md');
            if (file_exists($docPath)) {
                $content = file_get_contents($docPath);
                $parsedown = new \Parsedown();
                $docContent = $parsedown->text($content);
            } else {
                $docContent = '<div class="alert alert-warning">Arquivo de documentação não encontrado (docs/USER_MANUAL.md).</div>';
            }
        }

        return view('settings.index', compact('tab', 'docContent'));
    }
}
