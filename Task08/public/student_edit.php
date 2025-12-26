<?php
/**
 * Форма редактирования студента
 */

require_once __DIR__ . '/../config/database.php';

$studentId = $_GET['id'] ?? null;

if (!$studentId) {
    header('Location: index.php');
    exit;
}

// Получение данных студента
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
$stmt->execute([':id' => $studentId]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: index.php');
    exit;
}

// Получение текущей группы студента
$groupStmt = $pdo->prepare("SELECT group_id, academic_year 
                           FROM student_groups 
                           WHERE student_id = :student_id 
                           ORDER BY academic_year DESC 
                           LIMIT 1");
$groupStmt->execute([':student_id' => $studentId]);
$currentGroup = $groupStmt->fetch();

// Получение списка всех групп
$groupsQuery = $pdo->query("SELECT id, name, academic_year 
                           FROM groups 
                           ORDER BY name, academic_year");
$groups = $groupsQuery->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать студента</title>
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
        .radio-group {
            display: flex;
            gap: 20px;
        }
        .radio-group label {
            display: flex;
            align-items: center;
            font-weight: normal;
        }
        .radio-group input[type="radio"] {
            width: auto;
            margin-right: 5px;
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
    </style>
</head>
<body>
    <h1>Редактировать студента</h1>
    
    <form method="POST" action="student_save.php">
        <input type="hidden" name="id" value="<?= htmlspecialchars($student['id']) ?>">
        
        <div class="form-group">
            <label for="last_name">Фамилия *</label>
            <input type="text" id="last_name" name="last_name" 
                   value="<?= htmlspecialchars($student['last_name']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="first_name">Имя *</label>
            <input type="text" id="first_name" name="first_name" 
                   value="<?= htmlspecialchars($student['first_name']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="middle_name">Отчество</label>
            <input type="text" id="middle_name" name="middle_name" 
                   value="<?= htmlspecialchars($student['middle_name'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label for="birth_date">Дата рождения *</label>
            <input type="date" id="birth_date" name="birth_date" 
                   value="<?= htmlspecialchars($student['birth_date']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Пол *</label>
            <div class="radio-group">
                <label>
                    <input type="radio" name="gender" value="мужской" 
                           <?= $student['gender'] == 'мужской' ? 'checked' : '' ?> required>
                    Мужской
                </label>
                <label>
                    <input type="radio" name="gender" value="женский" 
                           <?= $student['gender'] == 'женский' ? 'checked' : '' ?> required>
                    Женский
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label for="group_id">Группа *</label>
            <select id="group_id" name="group_id" required>
                <option value="">Выберите группу</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= htmlspecialchars($group['id']) ?>"
                            <?= $currentGroup && $currentGroup['group_id'] == $group['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($group['name']) ?> (<?= htmlspecialchars($group['academic_year']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="academic_year">Учебный год *</label>
            <input type="text" id="academic_year" name="academic_year" 
                   value="<?= htmlspecialchars($currentGroup['academic_year'] ?? '') ?>"
                   placeholder="2023/2024" pattern="\d{4}/\d{4}" required>
        </div>
        
        <div class="buttons">
            <button type="submit">Сохранить</button>
            <a href="index.php" class="button">Отмена</a>
        </div>
    </form>
</body>
</html>

