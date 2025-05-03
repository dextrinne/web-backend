<?php
function getAbilities($db) {
    try {
        $abilities = [];
        $data = $db->query("SELECT id, name FROM language")->fetchAll();
        foreach ($data as $ability) {
            $abilities[$ability['id']] = $ability['name'];
        }
        return $abilities;
    } catch (PDOException $e) {
        die('Error getting abilities: ' . $e->getMessage());
    }
}

function getUserData($db, $login) {
    try {
        $stmt = $db->prepare("
            SELECT u.fio, u.tel, u.email, u.bdate, u.gender, u.bio, u.ccheck,
                   GROUP_CONCAT(ul.lang_id) as abilities
            FROM user u
            INNER JOIN user_login ulg ON u.id = ulg.user_id
            LEFT JOIN user_language ul ON u.id = ul.user_id
            WHERE ulg.login = ?
            GROUP BY u.id
        ");
        $stmt->execute([$login]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Error getting user data: ' . $e->getMessage());
    }
}

function getAllUsers($db) {
    try {
        $stmt = $db->prepare("
            SELECT u.id, u.fio, u.tel, u.email, u.bdate, u.gender, u.bio, u.ccheck,
                   GROUP_CONCAT(l.name SEPARATOR ', ') as languages
            FROM user u
            LEFT JOIN user_language ul ON u.id = ul.user_id
            LEFT JOIN language l ON ul.lang_id = l.id
            GROUP BY u.id
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Error getting all users: ' . $e->getMessage());
    }
}

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
        die('Error getting language stats: ' . $e->getMessage());
    }
}

function checkAdminAuth($db) {
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        header('HTTP/1.0 401 Unauthorized');
        die('Требуется авторизация');
    }

    try {
        $stmt = $db->prepare("SELECT password FROM admin WHERE login = ?");
        $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin || hash('sha256', $_SERVER['PHP_AUTH_PW']) !== $admin['password']) {
            header('WWW-Authenticate: Basic realm="Admin Panel"');
            header('HTTP/1.0 401 Unauthorized');
            die('Неверные учетные данные');
        }
    } catch (PDOException $e) {
        die('Ошибка проверки учетных данных: ' . $e->getMessage());
    }
}

function generateRandomString($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
?>
