<?php
/**
 * Форма добавления экзамена
 */

require_once __DIR__ . '/../config/database.php';

$studentId = $_GET['student_id'] ?? null;

if (!$studentId) {
    header('Location: index.php');
    exit;
}

// Получение данных студента
$studentStmt = $pdo->prepare("SELECT s.* FROM students s WHERE s.id = :id");
$studentStmt->execute([':id' => $studentId]);
$student = $studentStmt->fetch();

if (!$student) {
    header('Location: index.php');
    exit;
}

// Получение всех групп студента для выбора учебного года
$groupsStmt = $pdo->prepare("SELECT DISTINCT sg.academic_year, g.id, g.name, g.direction_id, dir.name as direction_name
                             FROM student_groups sg
                             INNER JOIN groups g ON sg.group_id = g.id
                             INNER JOIN directions dir ON g.direction_id = dir.id
                             WHERE sg.student_id = :student_id
                             ORDER BY sg.academic_year");
$groupsStmt->execute([':student_id' => $studentId]);
$studentGroups = $groupsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить экзамен</title>
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
        input[type="number"],
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
    <h1>Добавить экзамен</h1>
    
    <div class="info">
        <strong>Студент:</strong> <?= htmlspecialchars($student['last_name'] . ' ' . 
                                                       $student['first_name'] . ' ' . 
                                                       ($student['middle_name'] ?? '')) ?>
    </div>
    
    <form method="POST" action="exam_save.php" id="examForm">
        <input type="hidden" name="student_id" value="<?= htmlspecialchars($studentId) ?>">
        
        <div class="form-group">
            <label for="academic_year">Учебный год *</label>
            <select id="academic_year" name="academic_year" required>
                <option value="">Выберите учебный год</option>
                <?php foreach ($studentGroups as $sg): ?>
                    <option value="<?= htmlspecialchars($sg['academic_year']) ?>"
                            data-direction="<?= htmlspecialchars($sg['direction_id']) ?>">
                        <?= htmlspecialchars($sg['academic_year']) ?> 
                        (<?= htmlspecialchars($sg['direction_name']) ?>, группа <?= htmlspecialchars($sg['name']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="discipline_id">Дисциплина *</label>
            <select id="discipline_id" name="discipline_id" required>
                <option value="">Сначала выберите учебный год</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="grade">Оценка *</label>
            <select id="grade" name="grade" required>
                <option value="">Выберите оценку</option>
                <option value="5">5 (Отлично)</option>
                <option value="4">4 (Хорошо)</option>
                <option value="3">3 (Удовлетворительно)</option>
                <option value="2">2 (Неудовлетворительно)</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="exam_date">Дата экзамена *</label>
            <input type="date" id="exam_date" name="exam_date" 
                   value="<?= date('Y-m-d') ?>" required>
        </div>
        
        <div class="buttons">
            <button type="submit">Сохранить</button>
            <a href="exams.php?student_id=<?= $studentId ?>" class="button">Отмена</a>
        </div>
    </form>
    
    <script>
        // Загрузка дисциплин при выборе учебного года
        const academicYearSelect = document.getElementById('academic_year');
        const disciplineSelect = document.getElementById('discipline_id');
        
        function loadDisciplines() {
            const academicYear = academicYearSelect.value;
            
            if (!academicYear) {
                disciplineSelect.innerHTML = '<option value="">Сначала выберите учебный год</option>';
                return;
            }
            
            const selectedOption = academicYearSelect.options[academicYearSelect.selectedIndex];
            const directionId = selectedOption.getAttribute('data-direction');
            
            if (!directionId) {
                disciplineSelect.innerHTML = '<option value="">Ошибка: не указано направление</option>';
                return;
            }
            
            // Загрузка дисциплин через AJAX
            fetch('get_disciplines.php?direction_id=' + directionId + '&academic_year=' + academicYear)
                .then(response => response.json())
                .then(data => {
                    disciplineSelect.innerHTML = '<option value="">Выберите дисциплину</option>';
                    if (data.length === 0) {
                        disciplineSelect.innerHTML = '<option value="">Дисциплины не найдены для данного направления</option>';
                    } else {
                        data.forEach(discipline => {
                            const option = document.createElement('option');
                            option.value = discipline.study_plan_id;
                            option.textContent = discipline.discipline_name;
                            disciplineSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    disciplineSelect.innerHTML = '<option value="">Ошибка загрузки дисциплин</option>';
                });
        }
        
        academicYearSelect.addEventListener('change', loadDisciplines);
    </script>
</body>
</html>

