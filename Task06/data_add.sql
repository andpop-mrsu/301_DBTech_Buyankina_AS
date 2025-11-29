INSERT INTO users (first_name, last_name, email, gender, register_date, occupation_id)
SELECT 
	'Алина',
    'Буянкина',
    'buyankina@example.com',
    'male',
    datetime('now'),
    (SELECT id FROM occupations WHERE name = 'student');

INSERT INTO users (first_name, last_name, email, gender, register_date, occupation_id)
SELECT 
    'Данила',
    'Бабин',
    'bab@example.com',
    'male',
    datetime('now'),
    (SELECT id FROM occupations WHERE name = 'student');


INSERT INTO users (first_name, last_name, email, gender, register_date, occupation_id)
SELECT 
    'Никита',
    'Андронов',
    'andr@example.com',
    'female',
    datetime('now'),
    (SELECT id FROM occupations WHERE name = 'student');


INSERT INTO users (first_name, last_name, email, gender, register_date, occupation_id)
SELECT 
    'Альвина',
    'Кучина',
    'kych@example.com',
    'male',
    datetime('now'),
    (SELECT id FROM occupations WHERE name = 'programmer');

INSERT INTO users (first_name, last_name, email, gender, register_date, occupation_id)
SELECT 
    'Павел',
    'Голиков',
    'gol@example.com',
    'female',
    datetime('now'),
    (SELECT id FROM occupations WHERE name = 'student');

INSERT INTO movies (title, year)
VALUES ('Interstellar', 2014);

INSERT INTO movie_genres (movie_id, genre_id)
SELECT 
    (SELECT id FROM movies WHERE title = 'Interstellar' AND year = 2014),
    id
FROM genres
WHERE name IN ('Sci-Fi', 'Drama', 'Adventure');

INSERT INTO movies (title, year)
VALUES ('The Grand Budapest Hotel', 2014);

INSERT INTO movie_genres (movie_id, genre_id)
SELECT 
    (SELECT id FROM movies WHERE title = 'The Grand Budapest Hotel' AND year = 2014),
    id
FROM genres
WHERE name IN ('Comedy', 'Drama', 'Adventure');

INSERT INTO movies (title, year)
VALUES ('Inception', 2010);

INSERT INTO movie_genres (movie_id, genre_id)
SELECT 
    (SELECT id FROM movies WHERE title = 'Inception' AND year = 2010),
    id
FROM genres
WHERE name IN ('Action', 'Sci-Fi', 'Thriller');

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT 
    (SELECT id FROM users WHERE email = 'dmitry.artemev@example.com'),
    (SELECT id FROM movies WHERE title = 'Interstellar' AND year = 2014),
    5.0,
    strftime('%s', 'now');

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT 
    (SELECT id FROM users WHERE email = 'dmitry.artemev@example.com'),
    (SELECT id FROM movies WHERE title = 'The Grand Budapest Hotel' AND year = 2014),
    4.5,
    strftime('%s', 'now');

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT 
    (SELECT id FROM users WHERE email = 'dmitry.artemev@example.com'),
    (SELECT id FROM movies WHERE title = 'Inception' AND year = 2010),
    5.0,
    strftime('%s', 'now');

