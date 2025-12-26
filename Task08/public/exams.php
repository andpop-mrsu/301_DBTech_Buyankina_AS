<?php
/**
 * Страница результатов экзаменов студента
 */

require_once __DIR__ . '/../config/database.php';

$studentId = $_GET['student_id'] ?? null;

if (!$studentId) {
    header('Location: index.php');
    exit;
}

// Получение данных студента
$studentStmt = $pdo->prepare("SELECT s.*, g.name as group_name, sg.academic_year 
                             FROM students s
                             INNER JOIN student_groups sg ON s.id = sg.student_id
                             INNER JOIN groups g ON sg.group_id = g.id
                             WHERE s.id = :id
                             ORDER BY sg.academic_year DESC
                             LIMIT 1");
$studentStmt->execute([':id' => $studentId]);
$student = $studentStmt->fetch();

if (!$student) {
    header('Location: index.php');
    exit;
}

// Получение всех экзаменов студента в хронологическом порядке
$examsStmt = $pdo->prepare("SELECT e.id, e.grade, e.exam_date, e.academic_year,
                                   d.name as discipline_name,
                                   dir.name as direction_name
                            FROM exams e
                            INNER JOIN study_plans sp ON e.study_plan_id = sp.id
                            INNER JOIN disciplines d ON sp.discipline_id = d.id
                            INNER JOIN directions dir ON sp.direction_id = dir.id
                            WHERE e.student_id = :student_id
                            ORDER BY e.exam_date DESC, e.academic_year DESC");
$examsStmt->execute([':student_id' => $studentId]);
$exams = $examsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты экзаменов</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        .student-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #17a2b8;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .actions {
            white-space: nowrap;
        }
        .actions a {
            display: inline-block;
            padding: 5px 10px;
            margin: 0 3px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 12px;
        }
        .btn-edit {
            background-color: #28a745;
            color: white;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-add {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-add:hover {
            background-color: #218838;
        }
        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
        .grade {
            font-weight: bold;
            font-size: 16px;
        }
        .grade-5 { color: #28a745; }
        .grade-4 { color: #17a2b8; }
        .grade-3 { color: #ffc107; }
        .grade-2 { color: #dc3545; }
    </style>
</head>
<body>
    <a href="index.php" class="btn-back">← Назад к списку студентов</a>
    
    <h1>Результаты экзаменов</h1>
    
    <div class="student-info">
        <p><strong>Студент:</strong> <?= htmlspecialchars($student['last_name'] . ' ' . 
                                                          $student['first_name'] . ' ' . 
                                                          ($student['middle_name'] ?? '')) ?></p>
        <p><strong>Группа:</strong> <?= htmlspecialchars($student['group_name']) ?></p>
        <p><strong>Учебный год:</strong> <?= htmlspecialchars($student['academic_year']) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Дисциплина</th>
                <th>Направление</th>
                <th>Оценка</th>
                <th>Дата экзамена</th>
                <th>Учебный год</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($exams)): ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Экзамены не найдены</td>
                </tr>
            <?php else: ?>
                <?php foreach ($exams as $exam): ?>
                    <tr>
                        <td><?= htmlspecialchars($exam['discipline_name']) ?></td>
                        <td><?= htmlspecialchars($exam['direction_name']) ?></td>
                        <td>
                            <span class="grade grade-<?= $exam['grade'] ?>">
                                <?= htmlspecialchars($exam['grade']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($exam['exam_date']) ?></td>
                        <td><?= htmlspecialchars($exam['academic_year']) ?></td>
                        <td class="actions">
                            <a href="exam_edit.php?id=<?= $exam['id'] ?>&student_id=<?= $studentId ?>" class="btn-edit">Редактировать</a>
                            <a href="exam_delete.php?id=<?= $exam['id'] ?>&student_id=<?= $studentId ?>" class="btn-delete">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="exam_add.php?student_id=<?= $studentId ?>" class="btn-add">Добавить экзамен</a>
</body>
</html>

