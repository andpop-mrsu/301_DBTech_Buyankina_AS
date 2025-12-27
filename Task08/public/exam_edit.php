<?php
/**
 * Форма редактирования экзамена
 */

require_once __DIR__ . '/../config/database.php';

$examId = $_GET['id'] ?? null;
$studentId = $_GET['student_id'] ?? null;

if (!$examId || !$studentId) {
    header('Location: index.php');
    exit;
}

// Получение данных экзамена
$examStmt = $pdo->prepare("SELECT e.*, sp.direction_id, sp.discipline_id, d.name as discipline_name
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

// Получение данных студента
$studentStmt = $pdo->prepare("SELECT s.* FROM students s WHERE s.id = :id");
$studentStmt->execute([':id' => $studentId]);
$student = $studentStmt->fetch();

// Получение всех групп студента
$groupsStmt = $pdo->prepare("SELECT DISTINCT sg.academic_year, g.id, g.name, g.direction_id
                             FROM student_groups sg
                             INNER JOIN groups g ON sg.group_id = g.id
                             WHERE sg.student_id = :student_id
                             ORDER BY sg.academic_year");
$groupsStmt->execute([':student_id' => $studentId]);
$studentGroups = $groupsStmt->fetchAll();

// Получение всех групп
$allGroupsQuery = $pdo->query("SELECT id, name, academic_year, direction_id 
                               FROM groups 
                               ORDER BY name, academic_year");
$allGroups = $allGroupsQuery->fetchAll();

// Получение дисциплин для текущего направления
$disciplinesStmt = $pdo->prepare("SELECT sp.id as study_plan_id, d.name as discipline_name
                                  FROM study_plans sp
                                  INNER JOIN disciplines d ON sp.discipline_id = d.id
                                  WHERE sp.direction_id = :direction_id
                                  AND sp.assessment_type = 'экзамен'
                                  ORDER BY d.name");
$disciplinesStmt->execute([':direction_id' => $exam['direction_id']]);
$disciplines = $disciplinesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать экзамен</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
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
        }
        button[type="submit"] {
            background-color: #28a745;
            color: white;
        }
        button[type="submit"]:hover {
            background-color: #218838;
        }
        a.button {
            background-color: #6c757d;
            color: white;
        }
        a.button:hover {
            background-color: #5a6268;
        }
        .info {
            background-color: #d1ecf1;
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <h1>Редактировать экзамен</h1>
    
    <div class="info">
        <strong>Студент:</strong> <?= htmlspecialchars($student['last_name'] . ' ' . 
                                                       $student['first_name'] . ' ' . 
                                                       ($student['middle_name'] ?? '')) ?>
    </div>
    
    <form method="POST" action="exam_save.php">
        <input type="hidden" name="id" value="<?= htmlspecialchars($exam['id']) ?>">
        <input type="hidden" name="student_id" value="<?= htmlspecialchars($studentId) ?>">
        
        <div class="form-group">
            <label for="academic_year">Учебный год *</label>
            <input type="text" id="academic_year" name="academic_year" 
                   value="<?= htmlspecialchars($exam['academic_year']) ?>"
                   placeholder="2023/2024" pattern="\d{4}/\d{4}" required>
        </div>
        
        <div class="form-group">
            <label for="discipline_id">Дисциплина *</label>
            <select id="discipline_id" name="discipline_id" required>
                <?php foreach ($disciplines as $discipline): ?>
                    <option value="<?= htmlspecialchars($discipline['study_plan_id']) ?>"
                            <?= $exam['study_plan_id'] == $discipline['study_plan_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($discipline['discipline_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="grade">Оценка *</label>
            <select id="grade" name="grade" required>
                <option value="5" <?= $exam['grade'] == 5 ? 'selected' : '' ?>>5 (Отлично)</option>
                <option value="4" <?= $exam['grade'] == 4 ? 'selected' : '' ?>>4 (Хорошо)</option>
                <option value="3" <?= $exam['grade'] == 3 ? 'selected' : '' ?>>3 (Удовлетворительно)</option>
                <option value="2" <?= $exam['grade'] == 2 ? 'selected' : '' ?>>2 (Неудовлетворительно)</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="exam_date">Дата экзамена *</label>
            <input type="date" id="exam_date" name="exam_date" 
                   value="<?= htmlspecialchars($exam['exam_date']) ?>" required>
        </div>
        
        <div class="buttons">
            <button type="submit">Сохранить</button>
            <a href="exams.php?student_id=<?= $studentId ?>" class="button">Отмена</a>
        </div>
    </form>
</body>
</html>

