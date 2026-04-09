<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'project.context' => \App\Http\Middleware\ProjectContextMiddleware::class,
            'app.setup' => \App\Http\Middleware\EnsureAppIsSetup::class,
        ]);

        $middleware->appendToGroup('web', [
            'app.setup'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
