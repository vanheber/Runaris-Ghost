<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SystemSetting;

class GumroadService
{
    /**
     * The unique product permalink from Gumroad.
     */
    private $permalink;

    public function __construct()
    {
        // This will be set by the user or defined in ENV
        $this->permalink = 'runarisghost'; // Placeholder
    }

    /**
     * Verifies a license key against Gumroad's API.
     */
    public function verifyLicense(string $licenseKey): array
    {
        // Environment Bypass: If not in production, allow any key
        if ($this->shouldBypassLicense()) {
            SystemSetting::setSetting('gumroad_license', $licenseKey ?: 'DEV-BYPASS');
            SystemSetting::setSetting('is_licensed', 'true');
            SystemSetting::setSetting('license_email', 'dev@runaris.local');
            return ['status' => true, 'message' => 'Modo Desenvolvedor: Licença aceita automaticamente.'];
        }

        try {
            $response = Http::post('https://api.gumroad.com/v2/licenses/verify', [
                'product_permalink' => $this->permalink,
                'license_key' => $licenseKey,
                'increment_uses_count' => true,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['success']) && $data['success']) {
                SystemSetting::setSetting('gumroad_license', $licenseKey);
                SystemSetting::setSetting('is_licensed', 'true');
                SystemSetting::setSetting('license_email', $data['purchase']['email'] ?? 'unknown');
                
                return ['status' => true, 'message' => 'Licença validada com sucesso!'];
            }

            return ['status' => false, 'message' => $data['message'] ?? 'Chave de licença inválida.'];
        } catch (\Exception $e) {
            Log::error("Gumroad API Error: " . $e->getMessage());
            return ['status' => false, 'message' => 'Erro ao conectar com o Gumroad. Tente novamente mais tarde.'];
        }
    }

    /**
     * Check if app is fully licensed locally.
     */
    public function isLicensedLocally(): bool
    {
        if ($this->shouldBypassLicense()) {
            return true;
        }

        return SystemSetting::getSetting('is_licensed', 'false') === 'true';
    }

    /**
     * Determine if we should bypass the hard license check.
     */
    private function shouldBypassLicense(): bool
    {
        return config('app.env') !== 'production' || config('app.debug') === true;
    }
}
