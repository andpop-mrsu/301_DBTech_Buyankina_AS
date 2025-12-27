<?php
/**
 * Главная страница - список студентов
 */

require_once __DIR__ . '/../config/database.php';

// Проверка наличия базы данных
$dbPath = __DIR__ . '/../data/database.db';
if (!file_exists($dbPath)) {
    die('<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Ошибка</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 5px; }
        h1 { color: #721c24; }
        code { background-color: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="error">
        <h1>База данных не инициализирована</h1>
        <p>Для работы приложения необходимо сначала инициализировать базу данных.</p>
        <p><strong>Инструкция:</strong></p>
        <ol>
            <li>Откройте командную строку в папке <code>Task08</code></li>
            <li>Запустите команду: <code>init_database.bat</code></li>
            <li>Или вручную: <code>php\php.exe data\init_db.php</code></li>
        </ol>
        <p>После инициализации обновите эту страницу.</p>
    </div>
</body>
</html>');
}

// Получение параметров фильтрации
$filterGroupId = $_GET['group_id'] ?? null;

// Получение списка всех групп для фильтра
$groupsQuery = $pdo->query("SELECT DISTINCT g.id, g.name, g.academic_year 
                           FROM groups g 
                           ORDER BY g.name, g.academic_year");
$groups = $groupsQuery->fetchAll();

// Получаем последнюю группу для каждого студента
$query = "SELECT s.id, s.first_name, s.middle_name, s.last_name, 
                 s.birth_date, s.gender,
                 g.name as group_name, g.academic_year,
                 sg.academic_year as student_academic_year
          FROM students s
          INNER JOIN student_groups sg ON s.id = sg.student_id
          INNER JOIN groups g ON sg.group_id = g.id
          INNER JOIN (
              SELECT student_id, MAX(academic_year) as max_year
              FROM student_groups
              GROUP BY student_id
          ) latest ON s.id = latest.student_id AND sg.academic_year = latest.max_year
          WHERE 1=1";

$params = [];

if ($filterGroupId) {
    $query .= " AND g.id = :group_id";
    $params[':group_id'] = $filterGroupId;
}

$query .= " ORDER BY g.name, s.last_name, s.first_name";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список студентов</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        .filter-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .filter-section label {
            margin-right: 10px;
        }
        .filter-section select {
            padding: 5px 10px;
            font-size: 14px;
        }
        .filter-section button {
            padding: 5px 15px;
            margin-left: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .filter-section button:hover {
            background-color: #0056b3;
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
            background-color: #007bff;
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
        .btn-exams {
            background-color: #17a2b8;
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
    </style>
</head>
<body>
    <h1>Список студентов</h1>
    
    <div class="filter-section">
        <form method="GET" action="">
            <label for="group_id">Фильтр по группе:</label>
            <select name="group_id" id="group_id">
                <option value="">Все группы</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= htmlspecialchars($group['id']) ?>" 
                            <?= $filterGroupId == $group['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($group['name']) ?> (<?= htmlspecialchars($group['academic_year']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Применить фильтр</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ФИО</th>
                <th>Дата рождения</th>
                <th>Пол</th>
                <th>Группа</th>
                <th>Учебный год</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Студенты не найдены</td>
                </tr>
            <?php else: ?>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($student['last_name'] . ' ' . 
                                                 $student['first_name'] . ' ' . 
                                                 ($student['middle_name'] ?? '')) ?>
                        </td>
                        <td><?= htmlspecialchars($student['birth_date']) ?></td>
                        <td><?= htmlspecialchars($student['gender']) ?></td>
                        <td><?= htmlspecialchars($student['group_name']) ?></td>
                        <td><?= htmlspecialchars($student['student_academic_year']) ?></td>
                        <td class="actions">
                            <a href="student_edit.php?id=<?= $student['id'] ?>" class="btn-edit">Редактировать</a>
                            <a href="student_delete.php?id=<?= $student['id'] ?>" class="btn-delete">Удалить</a>
                            <a href="exams.php?student_id=<?= $student['id'] ?>" class="btn-exams">Результаты экзаменов</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="student_add.php" class="btn-add">Добавить студента</a>
</body>
</html>

