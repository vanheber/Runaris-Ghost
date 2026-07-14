<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RequirementsChecker;
use App\Services\EnvWriter;
use App\Services\UserSetupService;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InstallController extends Controller
{
    /**
     * Show the web installer wizard.
     */
    public function index()
    {
        // If already installed, redirect to home
        if (file_exists(storage_path('install.lock'))) {
            return redirect('/');
        }

        return view('install.index');
    }

    /**
     * Step 0: Check server requirements.
     */
    public function checkRequirements()
    {
        $checker = new RequirementsChecker();
        return response()->json($checker->check());
    }


    /**
     * Step 3: Test database connection (SQLite check).
     */
    public function testDatabase(Request $request)
    {
        try {
            // SQLite just needs a writable database directory
            $dbDir = database_path();
            if (!is_writable($dbDir)) {
                return response()->json([
                    'status' => false,
                    'message' => __("O diretório database/ não tem permissão de escrita. Por favor, ajuste as permissões para continuar.")
                ]);
            }
            return response()->json(['status' => true, 'message' => __('Tudo pronto! O SQLite pode ser inicializado.')]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('Erro ao verificar diretório:') . ' ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Step 3b: Configure database and run migrations (SQLite only).
     */
    public function configureDatabase(Request $request)
    {
        $driver = 'sqlite';
        $envWriter = new EnvWriter();

        try {
            $dbPath = database_path('database.sqlite');
            
            // Create the SQLite file if it doesn't exist
            if (!file_exists($dbPath)) {
                touch($dbPath);
            }

            $envValues = [
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE'   => $dbPath,
                'DB_HOST'       => '127.0.0.1',
                'DB_PORT'       => '3306', // Placeholder for Laravel defaults
            ];

            // Write to .env
            $envWriter->set($envValues);

            // Reload the configuration
            Artisan::call('config:clear');

            // Re-configure the database connection at runtime
            config(['database.default' => 'sqlite']);
            config(['database.connections.sqlite.database' => $dbPath]);

            DB::purge();
            DB::reconnect();

            // Run migrations
            Artisan::call('migrate', ['--force' => true]);

            // Generate APP_KEY if missing
            if (empty(config('app.key'))) {
                Artisan::call('key:generate', ['--force' => true]);
            }

            return response()->json([
                'status' => true,
                'message' => __('Banco de dados SQLite inicializado e pronto!')
            ]);
        } catch (\Exception $e) {
            Log::error("Install - DB Config Error: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => __('Erro ao inicializar SQLite:') . ' ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Step 4: Create admin user.
     */
    public function createAdmin(Request $request)
    {
        try {
            $userSetup = new UserSetupService();
            $user = $userSetup->createAdminUser($request);

            Auth::login($user);

            return response()->json(['status' => true, 'message' => __('Usuário administrador criado com sucesso!')]);
        } catch (\Exception $e) {
            Log::error("Install - Create Admin Error: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => __('Erro ao criar usuário:') . ' ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Step 5: Save AI key (optional).
     */
    public function saveAiKey(Request $request)
    {
        $request->validate(['gemini_api_key' => 'required|string']);
        SystemSetting::setSetting('gemini_api_key', $request->gemini_api_key);
        return response()->json(['status' => true]);
    }

    /**
     * Step 6: Finalize installation.
     */
    public function finalize()
    {
        try {
            // Create the install lock file
            file_put_contents(storage_path('install.lock'), json_encode([
                'installed_at' => now()->toIso8601String(),
                'php_version'  => PHP_VERSION,
                'app_version'  => config('app.version', '1.2.0'),
            ], JSON_PRETTY_PRINT));

            // Clear all caches
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');

            // Lock the .env file (remove write permissions)
            $envWriter = new EnvWriter();
            $envWriter->lockEnvFile();

            return response()->json(['status' => true, 'message' => __('Instalação finalizada!')]);
        } catch (\Exception $e) {
            Log::error("Install - Finalize Error: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => __('Erro na finalização:') . ' ' . $e->getMessage()
            ], 500);
        }
    }
}
