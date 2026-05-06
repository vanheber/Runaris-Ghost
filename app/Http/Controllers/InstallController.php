<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RequirementsChecker;
use App\Services\EnvWriter;
use App\Services\GumroadService;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
     * Step 2: Validate license key.
     */
    public function validateLicense(Request $request)
    {
        $request->validate(['license_key' => 'required|string']);

        $gumroad = app(GumroadService::class);
        $result = $gumroad->verifyLicense($request->license_key);

        return response()->json($result);
    }

    /**
     * Step 3: Test database connection.
     */
    public function testDatabase(Request $request)
    {
        $request->validate([
            'driver' => 'required|in:sqlite,mysql,pgsql',
        ]);

        $driver = $request->driver;

        try {
            if ($driver === 'sqlite') {
                // SQLite just needs a writable database directory
                $dbDir = database_path();
                if (!is_writable($dbDir)) {
                    return response()->json([
                        'status' => false,
                        'message' => "O diretório database/ não tem permissão de escrita."
                    ]);
                }
                return response()->json(['status' => true, 'message' => 'SQLite pronto para uso.']);
            }

            // MySQL or PostgreSQL
            $request->validate([
                'host'     => 'required|string',
                'port'     => 'required|numeric',
                'database' => 'required|string',
                'username' => 'required|string',
                'password' => 'nullable|string',
            ]);

            $config = [
                'driver'   => $driver,
                'host'     => $request->host,
                'port'     => $request->port,
                'database' => $request->database,
                'username' => $request->username,
                'password' => $request->password ?? '',
                'charset'  => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'   => '',
            ];

            // Test the connection
            config(['database.connections._install_test' => $config]);
            DB::connection('_install_test')->getPdo();
            DB::disconnect('_install_test');

            return response()->json(['status' => true, 'message' => 'Conexão estabelecida com sucesso!']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Falha na conexão: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Step 3b: Configure database and run migrations.
     */
    public function configureDatabase(Request $request)
    {
        $request->validate([
            'driver' => 'required|in:sqlite,mysql,pgsql',
        ]);

        $driver = $request->driver;
        $envWriter = new EnvWriter();

        try {
            $envValues = ['DB_CONNECTION' => $driver];

            if ($driver === 'sqlite') {
                $envValues['DB_DATABASE'] = database_path('database.sqlite');
                
                // Create the SQLite file if it doesn't exist
                $dbPath = database_path('database.sqlite');
                if (!file_exists($dbPath)) {
                    touch($dbPath);
                }
            } else {
                $request->validate([
                    'host'     => 'required|string',
                    'port'     => 'required|numeric',
                    'database' => 'required|string',
                    'username' => 'required|string',
                    'password' => 'nullable|string',
                ]);

                $envValues['DB_HOST'] = $request->host;
                $envValues['DB_PORT'] = $request->port;
                $envValues['DB_DATABASE'] = $request->database;
                $envValues['DB_USERNAME'] = $request->username;
                $envValues['DB_PASSWORD'] = $request->password ?? '';
            }

            // Write to .env
            $envWriter->set($envValues);

            // Reload the configuration
            Artisan::call('config:clear');

            // Re-configure the database connection at runtime
            if ($driver === 'sqlite') {
                config(['database.default' => 'sqlite']);
                config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
            } else {
                config(['database.default' => $driver]);
                config(["database.connections.{$driver}.host" => $request->host]);
                config(["database.connections.{$driver}.port" => $request->port]);
                config(["database.connections.{$driver}.database" => $request->database]);
                config(["database.connections.{$driver}.username" => $request->username]);
                config(["database.connections.{$driver}.password" => $request->password ?? '']);
            }

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
                'message' => 'Banco de dados configurado e migrations executadas com sucesso!'
            ]);
        } catch (\Exception $e) {
            Log::error("Install - DB Config Error: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Erro ao configurar banco: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Step 4: Create admin user.
     */
    public function createAdmin(Request $request)
    {
        try {
            $rules = [
                'name'  => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'use_password' => 'required',
            ];

            if ($request->use_password == 1) {
                $rules['password'] = 'required|string|min:8|confirmed';
            }

            $request->validate($rules);

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => ($request->use_password == 1)
                    ? Hash::make($request->password)
                    : Hash::make('no-password-' . str()->random(16)),
            ]);

            SystemSetting::setSetting('setup_completed', 'true');
            SystemSetting::setSetting('use_local_password', $request->use_password ? 'true' : 'false');

            Auth::login($user);

            return response()->json(['status' => true, 'message' => 'Usuário administrador criado com sucesso!']);
        } catch (\Exception $e) {
            Log::error("Install - Create Admin Error: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Erro ao criar usuário: ' . $e->getMessage()
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

            return response()->json(['status' => true, 'message' => 'Instalação finalizada!']);
        } catch (\Exception $e) {
            Log::error("Install - Finalize Error: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Erro na finalização: ' . $e->getMessage()
            ], 500);
        }
    }
}
