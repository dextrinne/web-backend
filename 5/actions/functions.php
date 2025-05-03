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

function generateRandomString($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>
