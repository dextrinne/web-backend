<?php
// Функция для получения списка языков программирования.
function getAbilities($db)
{
    try {
        $abilities = [];
        $data = $db->query("SELECT id, name FROM language")->fetchAll();
        foreach ($data as $ability) {
            $name = $ability['name'];
            $lang_id = $ability['id'];
            $abilities[$lang_id] = $name;
        }
        return $abilities;
    } catch (PDOException $e) {
        print('Error: ' . $e->getMessage());
        exit();
    }
}

// Функция для получения данных пользователя из базы данных
function getUserData($db, $login)
{
    try {
        $stmt = $db->prepare("
            SELECT 
                u.fio, 
                u.tel, 
                u.email, 
                u.bdate, 
                u.gender, 
                u.bio, 
                u.ccheck,
                GROUP_CONCAT(ul.lang_id) as abilities
            FROM user u
            INNER JOIN user_login ulg ON u.id = ulg.user_id
            LEFT JOIN user_language ul ON u.id = ul.user_id
            WHERE ulg.login = ?
            GROUP BY u.id
        ");
        $stmt->execute([$login]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user_data;
    } catch (PDOException $e) {
        print('Error: ' . $e->getMessage());
        exit();
    }
}

// Функция для получения всех пользователей
function getAllUsers($db) {
    try {
        $stmt = $db->prepare("
            SELECT 
                u.id, u.fio, u.tel, u.email, u.bdate, u.gender, u.bio, u.ccheck,
                GROUP_CONCAT(l.name SEPARATOR ', ') as languages
            FROM user u
            LEFT JOIN user_language ul ON u.id = ul.user_id
            LEFT JOIN language l ON ul.lang_id = l.id
            GROUP BY u.id
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Ошибка получения пользователей: ' . $e->getMessage());
    }
}

// Функция для получения статистики по языкам
function getLanguageStats($db) {
    try {
        $stmt = $db->prepare("
            SELECT l.name, COUNT(ul.user_id) as user_count
            FROM language l
            LEFT JOIN user_language ul ON l.id = ul.lang_id
            GROUP BY l.id
            ORDER BY user_count DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Ошибка получения статистики: ' . $e->getMessage());
    }
}
