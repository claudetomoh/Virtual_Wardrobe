@echo off
REM start_all.bat - wrapper for start_all.ps1 to help Windows users
REM usage: start_all.bat -Local -Seed -Test

setlocal
set SCRIPT_DIR=%~dp0
powershell -NoProfile -ExecutionPolicy Bypass -File "%SCRIPT_DIR%start_all.ps1" %*
endlocal