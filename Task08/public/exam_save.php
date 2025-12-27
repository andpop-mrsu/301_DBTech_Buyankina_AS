<?php
/**
 * Обработчик сохранения экзамена
 */

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$examId = $_POST['id'] ?? null;
$studentId = $_POST['student_id'] ?? null;
$studyPlanId = $_POST['discipline_id'] ?? null;
$grade = $_POST['grade'] ?? null;
$examDate = $_POST['exam_date'] ?? '';
$academicYear = trim($_POST['academic_year'] ?? '');

// Валидация
if (!$studentId || !$studyPlanId || !$grade || !$examDate || !$academicYear) {
    header('Location: ' . ($examId ? 'exam_edit.php?id=' . $examId . '&student_id=' . $studentId : 'exam_add.php?student_id=' . $studentId));
    exit;
}

// Проверка оценки
if (!in_array((int)$grade, [2, 3, 4, 5])) {
    header('Location: ' . ($examId ? 'exam_edit.php?id=' . $examId . '&student_id=' . $studentId : 'exam_add.php?student_id=' . $studentId));
    exit;
}

try {
    if ($examId) {
        // Обновление существующего экзамена
        $stmt = $pdo->prepare("UPDATE exams 
                               SET study_plan_id = :study_plan_id, 
                                   grade = :grade, 
                                   exam_date = :exam_date, 
                                   academic_year = :academic_year 
                               WHERE id = :id");
        $stmt->execute([
            ':id' => $examId,
            ':study_plan_id' => $studyPlanId,
            ':grade' => (int)$grade,
            ':exam_date' => $examDate,
            ':academic_year' => $academicYear
        ]);
    } else {
        // Добавление нового экзамена
        $stmt = $pdo->prepare("INSERT INTO exams (student_id, study_plan_id, grade, exam_date, academic_year) 
                               VALUES (:student_id, :study_plan_id, :grade, :exam_date, :academic_year)");
        $stmt->execute([
            ':student_id' => $studentId,
            ':study_plan_id' => $studyPlanId,
            ':grade' => (int)$grade,
            ':exam_date' => $examDate,
            ':academic_year' => $academicYear
        ]);
    }
    
    header('Location: exams.php?student_id=' . $studentId);
    exit;
} catch (PDOException $e) {
    die('Ошибка сохранения: ' . $e->getMessage());
}

