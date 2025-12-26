<?php
/**
 * Обработчик сохранения студента
 */

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$studentId = $_POST['id'] ?? null;
$lastName = trim($_POST['last_name'] ?? '');
$firstName = trim($_POST['first_name'] ?? '');
$middleName = trim($_POST['middle_name'] ?? '');
$birthDate = $_POST['birth_date'] ?? '';
$gender = $_POST['gender'] ?? '';
$groupId = $_POST['group_id'] ?? null;
$academicYear = trim($_POST['academic_year'] ?? '');

// Валидация
if (empty($lastName) || empty($firstName) || empty($birthDate) || empty($gender) || !$groupId || empty($academicYear)) {
    header('Location: ' . ($studentId ? 'student_edit.php?id=' . $studentId : 'student_add.php'));
    exit;
}

try {
    $pdo->beginTransaction();
    
    if ($studentId) {
        // Обновление существующего студента
        $stmt = $pdo->prepare("UPDATE students 
                               SET last_name = :last_name, 
                                   first_name = :first_name, 
                                   middle_name = :middle_name, 
                                   birth_date = :birth_date, 
                                   gender = :gender 
                               WHERE id = :id");
        $stmt->execute([
            ':id' => $studentId,
            ':last_name' => $lastName,
            ':first_name' => $firstName,
            ':middle_name' => $middleName ?: null,
            ':birth_date' => $birthDate,
            ':gender' => $gender
        ]);
        
        // Обновление связи с группой
        $groupStmt = $pdo->prepare("UPDATE student_groups 
                                    SET group_id = :group_id, 
                                        academic_year = :academic_year 
                                    WHERE student_id = :student_id 
                                    AND academic_year = :academic_year");
        $groupStmt->execute([
            ':student_id' => $studentId,
            ':group_id' => $groupId,
            ':academic_year' => $academicYear
        ]);
        
        // Если связи не было, создаем новую
        if ($groupStmt->rowCount() == 0) {
            $insertGroupStmt = $pdo->prepare("INSERT INTO student_groups (student_id, group_id, academic_year) 
                                             VALUES (:student_id, :group_id, :academic_year)");
            $insertGroupStmt->execute([
                ':student_id' => $studentId,
                ':group_id' => $groupId,
                ':academic_year' => $academicYear
            ]);
        }
    } else {
        // Добавление нового студента
        $stmt = $pdo->prepare("INSERT INTO students (last_name, first_name, middle_name, birth_date, gender) 
                               VALUES (:last_name, :first_name, :middle_name, :birth_date, :gender)");
        $stmt->execute([
            ':last_name' => $lastName,
            ':first_name' => $firstName,
            ':middle_name' => $middleName ?: null,
            ':birth_date' => $birthDate,
            ':gender' => $gender
        ]);
        
        $newStudentId = $pdo->lastInsertId();
        
        // Добавление связи с группой
        $groupStmt = $pdo->prepare("INSERT INTO student_groups (student_id, group_id, academic_year) 
                                   VALUES (:student_id, :group_id, :academic_year)");
        $groupStmt->execute([
            ':student_id' => $newStudentId,
            ':group_id' => $groupId,
            ':academic_year' => $academicYear
        ]);
    }
    
    $pdo->commit();
    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    $pdo->rollBack();
    die('Ошибка сохранения: ' . $e->getMessage());
}

