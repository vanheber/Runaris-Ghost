@echo off
setlocal enableextensions
title Runaris Ghost - MCP
cd /d "%~dp0"

rem ---------------------------------------------------------------------------
rem  Runaris Ghost - servidor MCP (Model Context Protocol)
rem  Processo separado do app: a interface continua em 127.0.0.1, este so
rem  responde /mcp na rede, e sempre exige o token do .env.
rem  ponytail: sem Node/Python. Usa o FrankenPHP que ja vem no pacote.
rem ---------------------------------------------------------------------------

set "PHP=%~dp0runtime\php.exe"
set "FRANKENPHP=%~dp0runtime\frankenphp.exe"
set "PHPRC=%~dp0runtime"
if not defined MCP_PORT set "MCP_PORT=7788"

if not exist "%PHP%" (
  echo [ERRO] Runtime nao encontrado. Extraia o pacote por completo, incluindo a pasta runtime.
  pause
  exit /b 1
)

if not exist "%FRANKENPHP%" (
  echo [ERRO] frankenphp.exe nao encontrado em runtime\.
  pause
  exit /b 1
)

rem cwd em runtime\ para o php.ini (extension_dir="ext") resolver certo.
cd /d "%~dp0runtime"

rem ---- token: gera na primeira vez e imprime URL + token ----
"%PHP%" "%~dp0artisan" mcp:token
if errorlevel 1 (
  echo [ERRO] Falha ao preparar o token do MCP.
  pause
  exit /b 1
)

rem ---- porta livre? ----
set "PORTSTATE="
for /f "usebackq delims=" %%S in (`"%PHP%" -r "echo @stream_socket_server('tcp://0.0.0.0:%MCP_PORT%')?'LIVRE':'OCUPADA';"`) do set "PORTSTATE=%%S"
if /i "%PORTSTATE%"=="OCUPADA" (
  echo [AVISO] A porta %MCP_PORT% ja esta em uso.
  echo         Feche o outro processo ou defina MCP_PORT antes de abrir este atalho.
  pause
  exit /b 1
)

echo.
echo   MCP ouvindo na porta %MCP_PORT%. Feche esta janela para encerrar.
echo   Se o Windows perguntar, permita o acesso em "Redes privadas".
echo.

"%FRANKENPHP%" php-server --root "%~dp0mcp" --listen 0.0.0.0:%MCP_PORT%

echo.
echo   Servidor MCP encerrado.
pause
endlocal
exit /b 0
