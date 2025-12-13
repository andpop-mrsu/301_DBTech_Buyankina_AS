DROP TABLE IF EXISTS credits;
DROP TABLE IF EXISTS exams;
DROP TABLE IF EXISTS student_groups;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS groups;
DROP TABLE IF EXISTS study_plans;
DROP TABLE IF EXISTS disciplines;
DROP TABLE IF EXISTS directions;

-- Направления подготовки
CREATE TABLE directions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    degree_level TEXT NOT NULL DEFAULT 'бакалавриат',
    CHECK(degree_level IN ('бакалавриат', 'магистратура', 'специалитет'))
);

-- Дисциплины
CREATE TABLE disciplines (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
);

-- Учебные планы (связь направления и дисциплины)
CREATE TABLE study_plans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    direction_id INTEGER NOT NULL,
    discipline_id INTEGER NOT NULL,
    lecture_hours INTEGER NOT NULL DEFAULT 0,
    practice_hours INTEGER NOT NULL DEFAULT 0,
    assessment_type TEXT NOT NULL DEFAULT 'экзамен',
    CHECK(lecture_hours >= 0),
    CHECK(practice_hours >= 0),
    CHECK(assessment_type IN ('экзамен', 'зачет')),
    FOREIGN KEY (direction_id) REFERENCES directions(id) ON DELETE CASCADE,
    FOREIGN KEY (discipline_id) REFERENCES disciplines(id) ON DELETE CASCADE,
    UNIQUE(direction_id, discipline_id)
);

-- Группы студентов
CREATE TABLE groups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    direction_id INTEGER NOT NULL,
    academic_year TEXT NOT NULL,
    UNIQUE(name, academic_year),
    FOREIGN KEY (direction_id) REFERENCES directions(id) ON DELETE RESTRICT
);

-- Студенты
CREATE TABLE students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    middle_name TEXT,
    last_name TEXT NOT NULL,
    birth_date TEXT NOT NULL,
    gender TEXT NOT NULL DEFAULT 'мужской',
    CHECK(gender IN ('мужской', 'женский')),
    CHECK(birth_date IS date(birth_date))
);

-- Связь студентов с группами (для хранения истории)
CREATE TABLE student_groups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    group_id INTEGER NOT NULL,
    academic_year TEXT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE RESTRICT
);

-- Экзамены (оценки)
CREATE TABLE exams (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    study_plan_id INTEGER NOT NULL,
    grade INTEGER NOT NULL,
    exam_date TEXT NOT NULL DEFAULT (date('now')),
    academic_year TEXT NOT NULL,
    CHECK(grade IN (2, 3, 4, 5)),
    CHECK(exam_date IS date(exam_date)),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (study_plan_id) REFERENCES study_plans(id) ON DELETE RESTRICT
);

-- Зачеты
CREATE TABLE credits (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    study_plan_id INTEGER NOT NULL,
    is_passed INTEGER NOT NULL DEFAULT 0,
    credit_date TEXT NOT NULL DEFAULT (date('now')),
    academic_year TEXT NOT NULL,
    CHECK(is_passed IN (0, 1)),
    CHECK(credit_date IS date(credit_date)),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (study_plan_id) REFERENCES study_plans(id) ON DELETE RESTRICT
);

CREATE INDEX idx_students_last_name ON students(last_name);
CREATE INDEX idx_students_first_name ON students(first_name);
CREATE INDEX idx_groups_name ON groups(name);
CREATE INDEX idx_groups_direction ON groups(direction_id);
CREATE INDEX idx_student_groups_student ON student_groups(student_id);
CREATE INDEX idx_student_groups_group ON student_groups(group_id);
CREATE INDEX idx_exams_student ON exams(student_id);
CREATE INDEX idx_exams_study_plan ON exams(study_plan_id);
CREATE INDEX idx_credits_student ON credits(student_id);
CREATE INDEX idx_credits_study_plan ON credits(study_plan_id);

-- Направления подготовки
INSERT INTO directions (name, degree_level) VALUES
('Прикладная математика и информатика', 'бакалавриат'),
('Математика и компьютерные науки', 'бакалавриат'),
('Прикладная математика и информатика', 'магистратура'),
('Математика', 'бакалавриат');

-- Дисциплины
INSERT INTO disciplines (name) VALUES
('Математический анализ'),
('Алгебра и геометрия'),
('Дискретная математика'),
('Программирование'),
('Базы данных'),
('Операционные системы'),
('Теория вероятностей'),
('Математическая статистика'),
('Дифференциальные уравнения'),
('Численные методы');

-- Учебный план для "Прикладная математика и информатика" (бакалавриат)
INSERT INTO study_plans (direction_id, discipline_id, lecture_hours, practice_hours, assessment_type) VALUES
(1, 1, 72, 72, 'экзамен'),  -- Математический анализ
(1, 2, 54, 54, 'экзамен'),  -- Алгебра и геометрия
(1, 3, 36, 36, 'экзамен'),  -- Дискретная математика
(1, 4, 36, 72, 'экзамен'),  -- Программирование
(1, 5, 36, 36, 'экзамен'),  -- Базы данных
(1, 6, 36, 36, 'зачет'),    -- Операционные системы
(1, 7, 54, 54, 'экзамен'),  -- Теория вероятностей
(1, 8, 36, 36, 'экзамен');  -- Математическая статистика

-- Учебный план для "Математика и компьютерные науки" (бакалавриат)
INSERT INTO study_plans (direction_id, discipline_id, lecture_hours, practice_hours, assessment_type) VALUES
(2, 1, 90, 90, 'экзамен'),  -- Математический анализ
(2, 2, 72, 72, 'экзамен'),  -- Алгебра и геометрия
(2, 3, 54, 54, 'экзамен'),  -- Дискретная математика
(2, 4, 36, 54, 'зачет'),    -- Программирование
(2, 7, 72, 72, 'экзамен'),  -- Теория вероятностей
(2, 9, 54, 54, 'экзамен');  -- Дифференциальные уравнения


-- Группы для направления "Прикладная математика и информатика" (бакалавриат)
INSERT INTO groups (name, direction_id, academic_year) VALUES
('303', 1, '2020/2021'),
('403', 1, '2021/2022'),
('503', 1, '2022/2023'),
('603', 1, '2023/2024'),
('304', 1, '2020/2021'),
('404', 1, '2021/2022');

-- Группы для направления "Математика и компьютерные науки" (бакалавриат)
INSERT INTO groups (name, direction_id, academic_year) VALUES
('301', 2, '2020/2021'),
('401', 2, '2021/2022'),
('501', 2, '2022/2023');

INSERT INTO students (first_name, middle_name, last_name, birth_date, gender) VALUES
('Иван', 'Петрович', 'Иванов', '2002-05-15', 'мужской'),
('Мария', 'Сергеевна', 'Петрова', '2002-08-20', 'женский'),
('Алексей', 'Александрович', 'Сидоров', '2002-03-10', 'мужской'),
('Елена', 'Дмитриевна', 'Козлова', '2002-11-25', 'женский'),
('Дмитрий', 'Владимирович', 'Смирнов', '2002-01-30', 'мужской'),
('Анна', 'Ивановна', 'Федорова', '2002-07-12', 'женский'),
('Сергей', 'Николаевич', 'Морозов', '2002-09-05', 'мужской'),
('Ольга', 'Андреевна', 'Волкова', '2002-04-18', 'женский'),
('Павел', 'Сергеевич', 'Новиков', '2002-12-08', 'мужской'),
('Татьяна', 'Викторовна', 'Лебедева', '2002-06-22', 'женский'),
('Андрей', 'Олегович', 'Соколов', '2002-02-14', 'мужской'),
('Наталья', 'Михайловна', 'Попова', '2002-10-03', 'женский');

-- Студенты группы 303 (2020/2021)
INSERT INTO student_groups (student_id, group_id, academic_year) VALUES
(1, 1, '2020/2021'),
(2, 1, '2020/2021'),
(3, 1, '2020/2021'),
(4, 1, '2020/2021');

-- Студенты группы 304 (2020/2021)
INSERT INTO student_groups (student_id, group_id, academic_year) VALUES
(5, 5, '2020/2021'),
(6, 5, '2020/2021'),
(7, 5, '2020/2021');

-- Студенты группы 301 (2020/2021)
INSERT INTO student_groups (student_id, group_id, academic_year) VALUES
(8, 7, '2020/2021'),
(9, 7, '2020/2021'),
(10, 7, '2020/2021');

-- Переход в следующие курсы (2021/2022)
INSERT INTO student_groups (student_id, group_id, academic_year) VALUES
(1, 2, '2021/2022'),  -- 303 -> 403
(2, 2, '2021/2022'),
(3, 2, '2021/2022'),
(4, 2, '2021/2022'),
(5, 6, '2021/2022'),  -- 304 -> 404
(6, 6, '2021/2022'),
(7, 6, '2021/2022'),
(8, 8, '2021/2022'),  -- 301 -> 401
(9, 8, '2021/2022'),
(10, 8, '2021/2022');

-- Новые студенты (2021/2022)
INSERT INTO student_groups (student_id, group_id, academic_year) VALUES
(11, 2, '2021/2022'),
(12, 6, '2021/2022');

-- Экзамены для студентов группы 303 (2020/2021)
INSERT INTO exams (student_id, study_plan_id, grade, exam_date, academic_year) VALUES
(1, 1, 5, '2021-01-15', '2020/2021'),  -- Математический анализ
(1, 2, 4, '2021-01-20', '2020/2021'),  -- Алгебра и геометрия
(1, 3, 5, '2021-01-25', '2020/2021'),  -- Дискретная математика
(1, 4, 5, '2021-01-30', '2020/2021'),  -- Программирование

(2, 1, 4, '2021-01-15', '2020/2021'),
(2, 2, 4, '2021-01-20', '2020/2021'),
(2, 3, 4, '2021-01-25', '2020/2021'),
(2, 4, 5, '2021-01-30', '2020/2021'),

(3, 1, 3, '2021-01-15', '2020/2021'),
(3, 2, 3, '2021-01-20', '2020/2021'),
(3, 3, 4, '2021-01-25', '2020/2021'),
(3, 4, 3, '2021-01-30', '2020/2021'),

(4, 1, 5, '2021-01-15', '2020/2021'),
(4, 2, 5, '2021-01-20', '2020/2021'),
(4, 3, 5, '2021-01-25', '2020/2021'),
(4, 4, 5, '2021-01-30', '2020/2021');

-- Экзамены для студентов группы 304 (2020/2021)
INSERT INTO exams (student_id, study_plan_id, grade, exam_date, academic_year) VALUES
(5, 1, 4, '2021-01-15', '2020/2021'),
(5, 2, 4, '2021-01-20', '2020/2021'),
(5, 3, 5, '2021-01-25', '2020/2021'),
(5, 4, 4, '2021-01-30', '2020/2021'),

(6, 1, 5, '2021-01-15', '2020/2021'),
(6, 2, 5, '2021-01-20', '2020/2021'),
(6, 3, 4, '2021-01-25', '2020/2021'),
(6, 4, 5, '2021-01-30', '2020/2021'),

(7, 1, 2, '2021-01-15', '2020/2021'),
(7, 2, 3, '2021-01-20', '2020/2021'),
(7, 3, 3, '2021-01-25', '2020/2021'),
(7, 4, 2, '2021-01-30', '2020/2021');

-- Экзамены для студентов группы 301 (2020/2021)
INSERT INTO exams (student_id, study_plan_id, grade, exam_date, academic_year) VALUES
(8, 9, 5, '2021-01-15', '2020/2021'),  -- Математический анализ (другое направление)
(8, 10, 4, '2021-01-20', '2020/2021'), -- Алгебра и геометрия
(8, 11, 5, '2021-01-25', '2020/2021'), -- Дискретная математика

(9, 9, 4, '2021-01-15', '2020/2021'),
(9, 10, 4, '2021-01-20', '2020/2021'),
(9, 11, 4, '2021-01-25', '2020/2021'),

(10, 9, 5, '2021-01-15', '2020/2021'),
(10, 10, 5, '2021-01-20', '2020/2021'),
(10, 11, 5, '2021-01-25', '2020/2021');

-- Экзамены для студентов группы 403 (2021/2022)
INSERT INTO exams (student_id, study_plan_id, grade, exam_date, academic_year) VALUES
(1, 5, 5, '2022-01-15', '2021/2022'),  -- Базы данных
(1, 7, 5, '2022-01-20', '2021/2022'),  -- Теория вероятностей
(1, 8, 5, '2022-01-25', '2021/2022'),  -- Математическая статистика

(2, 5, 4, '2022-01-15', '2021/2022'),
(2, 7, 4, '2022-01-20', '2021/2022'),
(2, 8, 4, '2022-01-25', '2021/2022'),

(3, 5, 3, '2022-01-15', '2021/2022'),
(3, 7, 3, '2022-01-20', '2021/2022'),
(3, 8, 4, '2022-01-25', '2021/2022'),

(4, 5, 5, '2022-01-15', '2021/2022'),
(4, 7, 5, '2022-01-20', '2021/2022'),
(4, 8, 5, '2022-01-25', '2021/2022'),

(11, 5, 4, '2022-01-15', '2021/2022'),
(11, 7, 4, '2022-01-20', '2021/2022'),
(11, 8, 5, '2022-01-25', '2021/2022');

-- Зачеты для студентов группы 303 (2020/2021)
INSERT INTO credits (student_id, study_plan_id, is_passed, credit_date, academic_year) VALUES
(1, 6, 1, '2020-12-20', '2020/2021'),  -- Операционные системы
(2, 6, 1, '2020-12-20', '2020/2021'),
(3, 6, 1, '2020-12-20', '2020/2021'),
(4, 6, 1, '2020-12-20', '2020/2021');

-- Зачеты для студентов группы 304 (2020/2021)
INSERT INTO credits (student_id, study_plan_id, is_passed, credit_date, academic_year) VALUES
(5, 6, 1, '2020-12-20', '2020/2021'),
(6, 6, 1, '2020-12-20', '2020/2021'),
(7, 6, 0, '2020-12-20', '2020/2021');  -- Не сдал

-- Зачеты для студентов группы 301 (2020/2021)
INSERT INTO credits (student_id, study_plan_id, is_passed, credit_date, academic_year) VALUES
(8, 12, 1, '2020-12-20', '2020/2021'),  -- Программирование
(9, 12, 1, '2020-12-20', '2020/2021'),
(10, 12, 1, '2020-12-20', '2020/2021');

-- Зачеты для студентов группы 403 (2021/2022)
INSERT INTO credits (student_id, study_plan_id, is_passed, credit_date, academic_year) VALUES
(1, 6, 1, '2021-12-20', '2021/2022'),
(2, 6, 1, '2021-12-20', '2021/2022'),
(3, 6, 1, '2021-12-20', '2021/2022'),
(4, 6, 1, '2021-12-20', '2021/2022'),
(11, 6, 1, '2021-12-20', '2021/2022');

