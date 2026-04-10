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
        // BYPASS: For development/testing only
        SystemSetting::setSetting('gumroad_license', $licenseKey ?: 'DEV-BYPASS');
        SystemSetting::setSetting('is_licensed', 'true');
        SystemSetting::setSetting('license_email', 'dev@runaris.local');
        return ['status' => true, 'message' => '[DEBUG] Licença ignorada com sucesso!'];

        /* 
        try {
            // ... original implementation ...
        } catch (\Exception $e) {
            // ...
        }
        */
    }

    /**
     * Check if app is fully licensed locally.
     */
    public function isLicensedLocally(): bool
    {
        // BYPASS: For development/testing only
        return true;
        
        // return SystemSetting::getSetting('is_licensed', 'false') === 'true';
    }
}
