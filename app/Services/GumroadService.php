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
     * Returns true if valid and updates the local state.
     */
    public function verifyLicense(string $licenseKey): array
    {
        try {
            // TEMPORARY BYPASS: For development testing only.
            // REMOVE THIS BLOCK BEFORE COMMERCIAL RELEASE.
            if ($licenseKey === 'DEV-BYPASS-GHOST' || env('APP_ENV') === 'local') {
                SystemSetting::setSetting('gumroad_license', 'DEV-BYPASS-ACTIVE');
                SystemSetting::setSetting('is_licensed', 'true');
                SystemSetting::setSetting('license_email', 'dev@runaris.local');
                return ['status' => true, 'message' => '[DEV MODE] Licença ignorada com sucesso!'];
            }

            $response = Http::post('https://api.gumroad.com/v2/licenses/verify', [
                'product_permalink' => $this->permalink,
                'license_key' => $licenseKey,
                'increment_uses_count' => 'true'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['success']) && $data['success'] === true && !isset($data['purchase']['refunded'])) {
                    // Valid license
                    SystemSetting::setSetting('gumroad_license', $licenseKey);
                    SystemSetting::setSetting('is_licensed', 'true');
                    SystemSetting::setSetting('license_email', $data['purchase']['email'] ?? null);

                    return ['status' => true, 'message' => 'Licença validada com sucesso!'];
                }
            }

            // Invalidation
            SystemSetting::setSetting('is_licensed', 'false');
            return ['status' => false, 'message' => 'Chave de licença inválida ou reembolsada.'];

        } catch (\Exception $e) {
            Log::error("Gumroad API Error: " . $e->getMessage());
            return ['status' => false, 'message' => 'Erro ao comunicar com a verificação de licença. Tente mais tarde.'];
        }
    }

    /**
     * Check if app is fully licensed locally.
     */
    public function isLicensedLocally(): bool
    {
        return SystemSetting::getSetting('is_licensed', 'false') === 'true';
    }
}
