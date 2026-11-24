@echo off
setlocal enableextensions
title Runaris Ghost - Reverter atualizacao
cd /d "%~dp0"

rem ---------------------------------------------------------------------------
rem  Restaura a versao anterior (snapshot guardado por start.bat antes de
rem  aplicar um update). Nao toca em storage/, database.sqlite nem .env.
rem  ponytail: rode com o servidor fechado (feche a janela do start.bat).
rem ---------------------------------------------------------------------------

set "PHP=%~dp0runtime\php.exe"
set "PHPRC=%~dp0runtime"
set "UPD=%~dp0storage\updates"

if not exist "%UPD%\previous" (
  echo Nenhuma versao anterior encontrada. Nada a reverter.
  pause
  exit /b 1
)

echo Revertendo para a versao anterior...
robocopy "%UPD%\previous" "%~dp0." /E /XF .env *.sqlite *.sqlite-wal *.sqlite-shm *.sqlite-journal /NFL /NDL /NJH /NJS /NP >nul
if errorlevel 8 (
  echo [ERRO] Falha ao restaurar a versao anterior.
  pause
  exit /b 1
)

rem cwd em runtime\ para o php.ini (extension_dir="ext") resolver certo.
cd /d "%~dp0runtime"
"%PHP%" "%~dp0artisan" migrate --force
echo Reversao concluida.
call "%~dp0start.bat"
