<?php
/**
 * Скрипт инициализации базы данных
 * Запускать один раз для создания БД из db_init.sql
 */

$dbPath = __DIR__ . '/database.db';
// Пробуем найти db_init.sql в разных местах
$sqlPath = __DIR__ . '/db_init.sql';
if (!file_exists($sqlPath)) {
    $sqlPath = __DIR__ . '/../../db_init.sql';
}

if (!file_exists($sqlPath)) {
    die("Файл db_init.sql не найден!\nИскали в:\n- " . __DIR__ . "/db_init.sql\n- " . __DIR__ . "/../../db_init.sql\n");
}

echo "Инициализация базы данных...\n";
echo "Файл SQL: $sqlPath\n";
echo "База данных: $dbPath\n\n";

try {
    // Удаляем старую базу данных если существует
    if (file_exists($dbPath)) {
        unlink($dbPath);
        echo "Старая база данных удалена.\n";
    }
    
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents($sqlPath);
    
    if (empty($sql)) {
        die("Файл db_init.sql пуст!\n");
    }
    
    // Удаляем комментарии и пустые строки, затем разбиваем на запросы
    $sql = preg_replace('/--.*$/m', '', $sql); // Удаляем однострочные комментарии
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Удаляем многострочные комментарии
    
    // Разбиваем на отдельные запросы по точке с запятой
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            $stmt = trim($stmt);
            return !empty($stmt) && strlen($stmt) > 0;
        }
    );
    
    echo "Найдено запросов: " . count($statements) . "\n\n";
    
    $pdo->exec('BEGIN TRANSACTION');
    
    $executed = 0;
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                $executed++;
            } catch (PDOException $e) {
                echo "Ошибка в запросе #" . ($index + 1) . ": " . $e->getMessage() . "\n";
                echo "Запрос: " . substr($statement, 0, 100) . "...\n";
                throw $e;
            }
        }
    }
    
    $pdo->exec('COMMIT');
    
    echo "\nБаза данных успешно инициализирована!\n";
    echo "Выполнено запросов: $executed\n";
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        try {
            $pdo->exec('ROLLBACK');
        } catch (PDOException $rollbackError) {
            // Игнорируем ошибки отката
        }
    }
    die("\nОшибка инициализации базы данных: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("\nОшибка: " . $e->getMessage() . "\n");
}

