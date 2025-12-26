<?php
/**
 * Подключение к базе данных
 */

$dbPath = __DIR__ . '/../data/database.db';

// Проверка существования файла БД
if (!file_exists($dbPath)) {
    // Если это не CLI, показываем HTML ошибку
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        die('<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Ошибка</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 5px; }
        h1 { color: #721c24; }
        code { background-color: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="error">
        <h1>База данных не инициализирована</h1>
        <p>Для работы приложения необходимо сначала инициализировать базу данных.</p>
        <p><strong>Инструкция:</strong></p>
        <ol>
            <li>Откройте командную строку в папке <code>Task08</code></li>
            <li>Запустите команду: <code>init_database.bat</code></li>
            <li>Или вручную: <code>php\php.exe data\init_db.php</code></li>
        </ol>
        <p>После инициализации обновите эту страницу.</p>
    </div>
</body>
</html>');
    } else {
        die("Ошибка: База данных не найдена по пути: $dbPath\nЗапустите скрипт инициализации: php data/init_db.php\n");
    }
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        die('<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Ошибка базы данных</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 5px; }
        h1 { color: #721c24; }
    </style>
</head>
<body>
    <div class="error">
        <h1>Ошибка подключения к базе данных</h1>
        <p>' . htmlspecialchars($e->getMessage()) . '</p>
    </div>
</body>
</html>');
    } else {
        die('Ошибка подключения к базе данных: ' . $e->getMessage() . "\n");
    }
}

