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

function updateUserData($db, $user_id, $post_data, $abilities) {
    try {
        $stmt = $db->prepare("
            UPDATE user 
            SET fio = ?, tel = ?, email = ?, bdate = ?, gender = ?, bio = ?, ccheck = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $post_data['fio'],
            $post_data['tel'],
            $post_data['email'],
            $post_data['bdate'],
            $post_data['radio'],
            $post_data['bio'],
            isset($post_data['ccheck']) ? 1 : 0,
            $user_id
        ]);
        
        $db->prepare("DELETE FROM user_language WHERE user_id = ?")->execute([$user_id]);
        
        if (!empty($abilities)) {
            $stmt = $db->prepare("INSERT INTO user_language (user_id, lang_id) VALUES (?, ?)");
            foreach ($abilities as $ability) {
                $stmt->execute([$user_id, $ability]);
            }
        }
        
        return true;
    } catch (PDOException $e) {
        error_log('Error updating user data: ' . $e->getMessage());
        return false;
    }
}

function fillUserValues($db, $login = null) {
    if ($login) {
        $user_data = getUserData($db, $login);
        if ($user_data) {
            return [
                'fio' => $user_data['fio'] ?? '',
                'tel' => $user_data['tel'] ?? '',
                'email' => $user_data['email'] ?? '',
                'bdate' => $user_data['bdate'] ?? '',
                'radio' => $user_data['gender'] ?? '',
                'bio' => $user_data['bio'] ?? '',
                'ccheck' => $user_data['ccheck'] ?? '',
                'abilities' => isset($user_data['abilities']) ? explode(',', $user_data['abilities']) : []
            ];
        }
    }
    
    return [
        'fio' => $_COOKIE['fio_value'] ?? '',
        'tel' => $_COOKIE['tel_value'] ?? '',
        'email' => $_COOKIE['email_value'] ?? '',
        'bdate' => $_COOKIE['bdate_value'] ?? '',
        'radio' => $_COOKIE['radio_value'] ?? '',
        'bio' => $_COOKIE['bio_value'] ?? '',
        'ccheck' => $_COOKIE['ccheck_value'] ?? '',
        'abilities' => isset($_COOKIE['abilities_value']) ? explode(',', $_COOKIE['abilities_value']) : []
    ];
}
?>
