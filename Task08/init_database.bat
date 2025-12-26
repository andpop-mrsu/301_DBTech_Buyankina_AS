@echo off
chcp 65001 >nul
echo Инициализация базы данных
echo.
cd /d %~dp0
php\php.exe data\init_db.php
if %errorlevel% == 0 (
    echo.
    echo База данных успешно инициализирована!
    pause
) else (
    echo.
    echo Ошибка инициализации базы данных!
    pause
    exit /b 1
)
