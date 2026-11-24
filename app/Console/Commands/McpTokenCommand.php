<?php

namespace App\Console\Commands;

use App\Services\EnvWriter;
use Illuminate\Console\Command;

/**
 * Gera (ou mostra) o token do servidor MCP e imprime como conectar.
 *
 * Rodado pelo start-mcp.bat antes de subir o servidor, para o autor não ter
 * que abrir arquivo nenhum: o token aparece na própria janela preta.
 */
class McpTokenCommand extends Command
{
    protected $signature = 'mcp:token {--reset : gera um token novo (invalida o anterior)}';

    protected $description = 'Mostra o token do MCP (e gera um na primeira execução)';

    public function handle(): int
    {
        $token = (string) config('mcp.token');

        if ($token === '' || $this->option('reset')) {
            $token = 'rg_' . bin2hex(random_bytes(24));

            try {
                $saved = app(EnvWriter::class)->set(['MCP_TOKEN' => $token]);
            } catch (\Throwable $e) {
                $saved = false;
            }

            if (!$saved) {
                $this->error('Não foi possível gravar MCP_TOKEN no .env (arquivo somente leitura?).');
                $this->line('  chmod u+w .env && php artisan mcp:token');

                return self::FAILURE;
            }

            config(['mcp.token' => $token]);
            $this->info($this->option('reset') ? 'Token novo gerado.' : 'Token gerado.');
        }

        $port = (int) config('mcp.port', 7788);
        $url = 'http://' . $this->lanIp() . ':' . $port . '/mcp';

        $this->newLine();
        $this->line('  Runaris Ghost — servidor MCP');
        $this->line('  URL:    ' . $url);
        $this->line('  Token:  ' . $token);
        $this->newLine();
        $this->line('  Para o agente usar, no opencode.json:');
        $this->newLine();
        $this->line(json_encode([
            'mcp' => [
                'runaris-ghost' => [
                    'type' => 'remote',
                    'url' => $url,
                    'enabled' => true,
                    'headers' => ['Authorization' => 'Bearer ' . $token],
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * IP da máquina na rede local, sem depender de DNS reverso.
     */
    protected function lanIp(): string
    {
        $host = gethostbyname(gethostname());

        if (filter_var($host, FILTER_VALIDATE_IP) && !str_starts_with($host, '127.')) {
            return $host;
        }

        // Abre um socket UDP: não envia pacote, só descobre a interface de saída.
        $socket = @stream_socket_client('udp://8.8.8.8:53', $errno, $errstr, 1);

        if ($socket) {
            $name = (string) stream_socket_get_name($socket, false);
            fclose($socket);

            if (str_contains($name, ':')) {
                return substr($name, 0, (int) strrpos($name, ':'));
            }
        }

        return 'IP-DESTA-MAQUINA';
    }
}
