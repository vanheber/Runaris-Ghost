@echo off
setlocal enableextensions
title Runaris Ghost
cd /d "%~dp0"

rem ---------------------------------------------------------------------------
rem  Runaris Ghost - launcher Windows (runtime FrankenPHP embutido)
rem  1) aplica atualizacao pendente (se houver)  2) acha porta livre
rem  3) serve public/  4) abre o navegador
rem  ponytail: nada de Node/PowerShell pra logica; so o zip ou Expand-Archive.
rem ---------------------------------------------------------------------------

set "PHP=%~dp0runtime\php.exe"
set "FRANKENPHP=%~dp0runtime\frankenphp.exe"
set "PHPRC=%~dp0runtime"
set "UPD=%~dp0storage\updates"

if not exist "%PHP%" (
  echo [ERRO] Runtime nao encontrado. Extraia o pacote por completo, incluindo a pasta runtime.
  pause
  exit /b 1
)

rem cwd fica em runtime\ para o php.ini (extension_dir="ext") resolver certo.
cd /d "%~dp0runtime"

rem ---- 1) Aplicar atualizacao pendente (servidor parado neste momento) ----
if exist "%UPD%\pending.zip" (
  echo Aplicando atualizacao...
  if exist "%UPD%\previous" rmdir /s /q "%UPD%\previous"
  mkdir "%UPD%\previous"
  robocopy "%~dp0." "%UPD%\previous" /E /XD "%~dp0storage" /XF .env *.sqlite *.sqlite-wal *.sqlite-shm *.sqlite-journal /NFL /NDL /NJH /NJS /NP >nul
  if errorlevel 8 (
    echo [ERRO] Falha ao preparar backup. Atualizacao cancelada.
    del "%UPD%\pending.zip" 2>nul
    pause
    exit /b 1
  )
  powershell -NoProfile -ExecutionPolicy Bypass -Command "Expand-Archive -Force -LiteralPath '%UPD%\pending.zip' -DestinationPath '%~dp0'"
  if errorlevel 1 goto :update_fail
  "%PHP%" "%~dp0artisan" migrate --force
  if errorlevel 1 goto :update_fail
  del "%UPD%\pending.zip" 2>nul
  echo Atualizacao aplicada com sucesso.
)

rem ---- 2) Porta livre, escolhida pelo proprio PHP ----
set "PORT="
for /f "usebackq delims=" %%P in (`"%PHP%" -r "echo (function(){ $s=@stream_socket_server('tcp://127.0.0.1:0',$e,$m); if(!$s){return 8000;} $n=stream_socket_get_name($s,false); fclose($s); return substr($n,strrpos($n,':')+1); })();"`) do set "PORT=%%P"
if not defined PORT set "PORT=8000"
set "APP_URL=http://127.0.0.1:%PORT%"

echo.
echo   Runaris Ghost  ^>^>  http://127.0.0.1:%PORT%
echo   Feche esta janela para encerrar o programa.
echo.

rem ---- 3) Servir + abrir navegador ----
start "" "http://127.0.0.1:%PORT%"
"%FRANKENPHP%" php-server --root "%~dp0public" --listen 127.0.0.1:%PORT%

endlocal
exit /b 0

:update_fail
echo [ERRO] Falha ao aplicar a atualizacao. Restaurando a versao anterior...
robocopy "%UPD%\previous" "%~dp0." /E /XF .env *.sqlite *.sqlite-wal *.sqlite-shm *.sqlite-journal /NFL /NDL /NJH /NJS /NP >nul
del "%UPD%\pending.zip" 2>nul
pause
exit /b 1
