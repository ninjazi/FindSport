-- Создаем временную таблицу для хранения информации о клиентах на дубликатах
CREATE TEMPORARY TABLE temp_session_members AS
SELECT DISTINCT sm1.session_id, sm1.client_id
FROM session_members sm1
JOIN sessions s1 ON sm1.session_id = s1.id
JOIN sessions s2 ON s1.start_time = s2.start_time
               AND s1.session_configuration_id = s2.session_configuration_id
               AND s1.id < s2.id;

-- Удаляем дубликаты из таблицы sessions
DELETE s1
FROM sessions s1
JOIN sessions s2 ON s1.start_time = s2.start_time
               AND s1.session_configuration_id = s2.session_configuration_id
               AND s1.id < s2.id;

-- Вставляем обратно отметки клиентов на оригинальные занятия
INSERT INTO session_members (session_id, client_id)
SELECT DISTINCT sm.session_id, sm.client_id
FROM temp_session_members sm
JOIN sessions s ON sm.session_id = s.id;

-- Удаляем временную таблицу
DROP TEMPORARY TABLE IF EXISTS temp_session_members;

