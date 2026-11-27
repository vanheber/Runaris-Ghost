<?php

/**
 * Shim do Artisan para o runtime embutido do macOS/Linux.
 *
 * O `frankenphp php-cli script.php ...` até a v1.13 entrega o PRÓPRIO binário
 * em $argv[0] e o script em $argv[1], enquanto o Symfony ArgvInput remove só
 * um elemento — resultado: `Command "artisan" is not defined`. Aqui a lista é
 * normalizada para o formato que o artisan espera e só então ele é carregado.
 *
 * Se um FrankenPHP futuro passar a se comportar igual ao PHP do sistema
 * (script em $argv[0]), a condição não casa e nada é alterado.
 */

$argv = $_SERVER['argv'] ?? [];
$script = $argv[1] ?? null;

if ($script !== null && realpath($script) === __FILE__) {
    array_shift($argv);      // remove o binário do FrankenPHP
    $argv[0] = __FILE__;     // o script volta à posição que o Artisan espera
}

$_SERVER['argv'] = $argv;
$GLOBALS['argv'] = $argv;

require dirname(__DIR__) . '/artisan';
