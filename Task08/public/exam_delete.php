<?php
/**
 * Обработчик удаления экзамена
 */

require_once __DIR__ . '/../config/database.php';

$examId = $_GET['id'] ?? null;
$studentId = $_GET['student_id'] ?? null;

if (!$examId || !$studentId) {
    header('Location: index.php');
    exit;
}

// Обработка удаления
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    try {
        $deleteStmt = $pdo->prepare("DELETE FROM exams WHERE id = :id");
        $deleteStmt->execute([':id' => $examId]);
        
        header('Location: exams.php?student_id=' . $studentId);
        exit;
    } catch (PDOException $e) {
        die('Ошибка удаления: ' . $e->getMessage());
    }
}

// Получение данных экзамена для подтверждения
$examStmt = $pdo->prepare("SELECT e.*, d.name as discipline_name
                           FROM exams e
                           INNER JOIN study_plans sp ON e.study_plan_id = sp.id
                           INNER JOIN disciplines d ON sp.discipline_id = d.id
                           WHERE e.id = :id");
$examStmt->execute([':id' => $examId]);
$exam = $examStmt->fetch();

if (!$exam) {
    header('Location: exams.php?student_id=' . $studentId);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удалить экзамен</title>
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
        .exam-info {
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
    <h1>Удалить экзамен</h1>
    
    <div class="warning">
        <strong>Внимание!</strong> Вы уверены, что хотите удалить эту запись об экзамене?
    </div>
    
    <div class="exam-info">
        <p><strong>Дисциплина:</strong> <?= htmlspecialchars($exam['discipline_name']) ?></p>
        <p><strong>Оценка:</strong> <?= htmlspecialchars($exam['grade']) ?></p>
        <p><strong>Дата экзамена:</strong> <?= htmlspecialchars($exam['exam_date']) ?></p>
        <p><strong>Учебный год:</strong> <?= htmlspecialchars($exam['academic_year']) ?></p>
    </div>
    
    <form method="POST" action="">
        <div class="buttons">
            <button type="submit" name="confirm" value="1">Да, удалить</button>
            <a href="exams.php?student_id=<?= $studentId ?>" class="button">Отмена</a>
        </div>
    </form>
</body>
</html>

