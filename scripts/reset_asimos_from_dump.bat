@echo off
REM Reset asimos DB from asimos-sql-04-09-2026.sql
REM Run from project root: scripts\reset_asimos_from_dump.bat

set MYSQL=c:\xampp\mysql\bin\mysql.exe
set ROOT=%~dp0..
set DB=asimos

echo Dropping all tables...
"%MYSQL%" -u root %DB% < "%ROOT%\scripts\drop_asimos.sql"
if errorlevel 1 goto fail

echo Importing reference dump...
"%MYSQL%" -u root %DB% < "%ROOT%\asimos-sql-04-09-2026.sql"
if errorlevel 1 goto fail

echo Done. Database reset from asimos-sql-04-09-2026.sql
exit /b 0

:fail
echo Reset failed.
exit /b 1
