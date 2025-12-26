<?php
/**
 * API для получения списка дисциплин по направлению и учебному году
 */

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$directionId = $_GET['direction_id'] ?? null;
$academicYear = $_GET['academic_year'] ?? null;

if (!$directionId || !$academicYear) {
    echo json_encode([]);
    exit;
}

// Определение курса на основе учебного года
// Предполагаем, что первый курс начинается в 2020/2021
$yearParts = explode('/', $academicYear);
$startYear = (int)$yearParts[0];
$baseYear = 2020;
$course = $startYear - $baseYear + 1;

// Получение дисциплин для данного направления
// Для упрощения показываем все дисциплины направления, которые могут быть на любом курсе
$stmt = $pdo->prepare("SELECT sp.id as study_plan_id, d.name as discipline_name, sp.assessment_type
                       FROM study_plans sp
                       INNER JOIN disciplines d ON sp.discipline_id = d.id
                       WHERE sp.direction_id = :direction_id
                       AND sp.assessment_type = 'экзамен'
                       ORDER BY d.name");
$stmt->execute([':direction_id' => $directionId]);
$disciplines = $stmt->fetchAll();

echo json_encode($disciplines);

