<?php
/**
 * Форма удаления студента
 */

require_once __DIR__ . '/../config/database.php';

$studentId = $_GET['id'] ?? null;

if (!$studentId) {
    header('Location: index.php');
    exit;
}

// Получение данных студента
$stmt = $pdo->prepare("SELECT s.*, g.name as group_name, sg.academic_year 
                      FROM students s
                      INNER JOIN student_groups sg ON s.id = sg.student_id
                      INNER JOIN groups g ON sg.group_id = g.id
                      WHERE s.id = :id
                      ORDER BY sg.academic_year DESC
                      LIMIT 1");
$stmt->execute([':id' => $studentId]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: index.php');
    exit;
}

// Обработка удаления
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    try {
        $pdo->beginTransaction();
        
        // Удаление студента (каскадное удаление связей)
        $deleteStmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
        $deleteStmt->execute([':id' => $studentId]);
        
        $pdo->commit();
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die('Ошибка удаления: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удалить студента</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #dc3545;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .student-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .buttons {
            margin-top: 20px;
        }
        button, a.button {
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        button[type="submit"] {
            background-color: #dc3545;
            color: white;
        }
        button[type="submit"]:hover {
            background-color: #c82333;
        }
        a.button {
            background-color: #6c757d;
            color: white;
        }
        a.button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <h1>Удалить студента</h1>
    
    <div class="warning">
        <strong>Внимание!</strong> Вы уверены, что хотите удалить этого студента? 
        Это действие также удалит все связанные записи об экзаменах и зачетах.
    </div>
    
    <div class="student-info">
        <p><strong>ФИО:</strong> <?= htmlspecialchars($student['last_name'] . ' ' . 
                                                       $student['first_name'] . ' ' . 
                                                       ($student['middle_name'] ?? '')) ?></p>
        <p><strong>Дата рождения:</strong> <?= htmlspecialchars($student['birth_date']) ?></p>
        <p><strong>Пол:</strong> <?= htmlspecialchars($student['gender']) ?></p>
        <p><strong>Группа:</strong> <?= htmlspecialchars($student['group_name']) ?></p>
        <p><strong>Учебный год:</strong> <?= htmlspecialchars($student['academic_year']) ?></p>
    </div>
    
    <form method="POST" action="">
        <div class="buttons">
            <button type="submit" name="confirm" value="1">Да, удалить</button>
            <a href="index.php" class="button">Отмена</a>
        </div>
    </form>
</body>
</html>

