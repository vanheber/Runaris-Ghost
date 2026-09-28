<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'project.context' => \App\Http\Middleware\ProjectContextMiddleware::class,
            'app.setup' => \App\Http\Middleware\EnsureAppIsSetup::class,
            'app.installed' => \App\Http\Middleware\EnsureAppIsInstalled::class,
        ]);

        $middleware->appendToGroup('web', [
            'app.installed',
            'app.setup',
        ]);

        $middleware->validateCsrfTokens(except: [
            'install/*',
            'settings/factory-reset/step-*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// ponytail: a dist chega sem .env — cria a partir do example e semeia APP_KEY (aleatório,
// por instalação) antes do LoadEnvironmentVariables. Sem isso o Encrypter estoura
// MissingAppKeyException na 1ª request e o wizard de instalação nunca abre.
// .env já existente (DEV / instalação atualizada por zip) nunca é tocado.
$envFile = dirname(__DIR__).'/.env';
if (!file_exists($envFile)) {
    @copy(dirname(__DIR__).'/.env.example', $envFile);
}
if (file_exists($envFile)) {
    $env = file_get_contents($envFile);
    if (preg_match('/^APP_KEY=(.*)$/m', $env, $m)) {
        if (trim($m[1], '"') === '') {
            file_put_contents($envFile, preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=base64:'.base64_encode(random_bytes(32)), $env, 1));
        }
    } else {
        file_put_contents($envFile, rtrim($env)."\nAPP_KEY=base64:".base64_encode(random_bytes(32))."\n");
    }
}

return $app;
