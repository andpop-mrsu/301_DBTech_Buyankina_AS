@echo off
chcp 65001 > nul
echo Запуск веб-приложения
echo.

REM Проверка наличия базы данных
if not exist "data\database.db" (
    echo База данных не найдена. Инициализация...
    call init_database.bat
    if errorlevel 1 (
        echo Ошибка инициализации базы данных!
        pause
        exit /b 1
    )
    echo.
)

REM Запуск сервера
echo Запуск веб-сервера на http://localhost:3000
echo Нажмите Ctrl+C для остановки
echo.
php\php.exe -S localhost:3000 -t public

