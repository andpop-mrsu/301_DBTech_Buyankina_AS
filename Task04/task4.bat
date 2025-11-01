#!/bin/bash

chcp 65001

sqlite3 movies_rating.db < db_init.sql

echo "1. Найти все пары пользователей, оценивших один и тот же фильм. Устранить дубликаты, проверить отсутствие пар с самим собой. Для каждой пары должны быть указаны имена пользователей и название фильма, который они оценили. В списке оставить первые 100 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT DISTINCT 
    CASE WHEN u1.name < u2.name THEN u1.name ELSE u2.name END AS user1,
    CASE WHEN u1.name < u2.name THEN u2.name ELSE u1.name END AS user2,
    m.title AS movie_title
FROM ratings r1
JOIN ratings r2 ON r1.movie_id = r2.movie_id AND r1.user_id < r2.user_id
JOIN users u1 ON r1.user_id = u1.id
JOIN users u2 ON r2.user_id = u2.id
JOIN movies m ON r1.movie_id = m.id
ORDER BY user1, user2, movie_title
LIMIT 100;"

echo " "

echo "2. Найти 10 самых свежих оценок от разных пользователей, вывести названия фильмов, имена пользователей, оценку, дату отзыва в формате ГГГГ-ММ-ДД."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "WITH ranked_ratings AS (
    SELECT 
        r.id,
        r.user_id,
        r.movie_id,
        r.rating,
        r.timestamp,
        ROW_NUMBER() OVER (PARTITION BY r.user_id ORDER BY r.timestamp DESC) AS rn
    FROM ratings r
)
SELECT
    m.title AS movie_title,
    u.name AS user_name,
    rr.rating,
    date(datetime(CAST(rr.timestamp AS INTEGER), 'unixepoch')) AS review_date
FROM ranked_ratings rr
JOIN movies m ON rr.movie_id = m.id
JOIN users u ON rr.user_id = u.id
WHERE rr.rn = 1
ORDER BY rr.timestamp DESC
LIMIT 10;"

echo " "

echo "3. Вывести в одном списке все фильмы с максимальным средним рейтингом и все фильмы с минимальным средним рейтингом. Общий список отсортировать по году выпуска и названию фильма. В зависимости от рейтинга в колонке \"Рекомендуем\" для фильмов должно быть написано \"Да\" или \"Нет\"."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "WITH avg_ratings AS (
    SELECT 
        m.id,
        m.title,
        m.year,
        AVG(r.rating) AS avg_rating
    FROM movies m
    JOIN ratings r ON m.id = r.movie_id
    GROUP BY m.id, m.title, m.year
),
min_max_ratings AS (
    SELECT 
        MIN(avg_rating) AS min_rating,
        MAX(avg_rating) AS max_rating
    FROM avg_ratings
)
SELECT 
    ar.title,
    ar.year,
    ar.avg_rating,
    CASE 
        WHEN ar.avg_rating = mmr.max_rating THEN 'Да'
        ELSE 'Нет'
    END AS Рекомендуем
FROM avg_ratings ar
CROSS JOIN min_max_ratings mmr
WHERE ar.avg_rating = mmr.min_rating OR ar.avg_rating = mmr.max_rating
ORDER BY ar.year, ar.title;"

echo " "

echo "4. Вычислить количество оценок и среднюю оценку, которую дали фильмам пользователи-женщины в период с 2010 по 2012 год."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT 
    COUNT(*) AS количество_оценок,
    AVG(r.rating) AS средняя_оценка
FROM ratings r
JOIN users u ON r.user_id = u.id
WHERE u.gender = 'F'
AND CAST(strftime('%Y', datetime(CAST(r.timestamp AS INTEGER), 'unixepoch')) AS INTEGER) BETWEEN 2010 AND 2012;"

echo " "

echo "5. Составить список фильмов с указанием их средней оценки и места в рейтинге по средней оценке. Полученный список отсортировать по году выпуска и названиям фильмов. В списке оставить первые 20 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "WITH movie_ratings AS (
    SELECT 
        m.id,
        m.title,
        m.year,
        AVG(r.rating) AS avg_rating,
        ROW_NUMBER() OVER (ORDER BY AVG(r.rating) DESC) AS rating_place
    FROM movies m
    JOIN ratings r ON m.id = r.movie_id
    GROUP BY m.id, m.title, m.year
)
SELECT 
    title,
    year,
    avg_rating,
    rating_place
FROM movie_ratings
ORDER BY year, title
LIMIT 20;"

echo " "

echo "6. Вывести список из 10 последних зарегистрированных пользователей в формате \"Фамилия Имя|Дата регистрации\" (сначала фамилия, потом имя)."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT 
    CASE 
        WHEN INSTR(name, ' ') > 0 THEN
            SUBSTR(name, INSTR(name, ' ') + 1) || ' ' || SUBSTR(name, 1, INSTR(name, ' ') - 1)
        ELSE name
    END || '|' || register_date AS user_info
FROM users
ORDER BY register_date DESC
LIMIT 10;"

echo " "

echo "7. С помощью рекурсивного CTE составить таблицу умножения для чисел от 1 до 10. Должен получиться один столбец следующего вида: 1x1=1, 1x2=2, ..., 1x10=10, 2x1=2, 2x2=4, ..., 10x9=90, 10x10=100."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "WITH RECURSIVE multiplication_table AS (
    SELECT 1 AS a, 1 AS b
    UNION ALL
    SELECT 
        CASE WHEN b >= 10 THEN a + 1 ELSE a END,
        CASE WHEN b >= 10 THEN 1 ELSE b + 1 END
    FROM multiplication_table
    WHERE a <= 10 AND (a < 10 OR b < 10)
)
SELECT CAST(a AS TEXT) || 'x' || CAST(b AS TEXT) || '=' || CAST(a * b AS TEXT) AS expression
FROM multiplication_table
WHERE a <= 10 AND b <= 10
ORDER BY a, b;"

echo " "

echo "8. С помощью рекурсивного CTE выделить все жанры фильмов, имеющиеся в таблице movies (каждый жанр в отдельной строке)."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "WITH RECURSIVE split_genres(id, remaining, genre) AS (
    SELECT 
        id,
        genres || '|',
        ''
    FROM movies
    WHERE genres IS NOT NULL AND genres != ''
    UNION ALL
    SELECT 
        id,
        SUBSTR(remaining, INSTR(remaining, '|') + 1),
        SUBSTR(remaining, 1, CASE WHEN INSTR(remaining, '|') > 0 THEN INSTR(remaining, '|') - 1 ELSE LENGTH(remaining) END)
    FROM split_genres
    WHERE remaining != ''
),
all_genres AS (
    SELECT DISTINCT genre
    FROM split_genres
    WHERE genre != ''
)
SELECT genre
FROM all_genres
ORDER BY genre;"
