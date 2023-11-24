<?php
try {
    $pdo = new PDO('mysql:host=ваш_хост;dbname=ваша_база_данных', 'ваше_имя_пользователя', 'ваш_пароль');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Создаем временную таблицу для хранения информации о клиентах на дубликатах
    $pdo->exec('CREATE TEMPORARY TABLE temp_session_members AS
                SELECT DISTINCT sm1.session_id, sm1.client_id
                FROM session_members sm1
                JOIN sessions s1 ON sm1.session_id = s1.id
                JOIN sessions s2 ON s1.start_time = s2.start_time
                               AND s1.session_configuration_id = s2.session_configuration_id
                               AND s1.id < s2.id');

    // Удаляем дубликаты из таблицы sessions
    $pdo->exec('DELETE s1
                FROM sessions s1
                JOIN sessions s2 ON s1.start_time = s2.start_time
                               AND s1.session_configuration_id = s2.session_configuration_id
                               AND s1.id < s2.id');

    // Вставляем обратно отметки клиентов на оригинальные занятия
    $pdo->exec('INSERT INTO session_members (session_id, client_id)
                SELECT DISTINCT sm.session_id, sm.client_id
                FROM temp_session_members sm
                JOIN sessions s ON sm.session_id = s.id');

    // Удаляем временную таблицу
    $pdo->exec('DROP TEMPORARY TABLE IF EXISTS temp_session_members');

    echo "Операции успешно выполнены.";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}