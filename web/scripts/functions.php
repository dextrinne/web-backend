<?php
/**
 * @param string $filename 
 * @param array $data 
 */
if (!function_exists('include_frontend_file')) {
    function include_frontend_file(string $filename, array $data = []): void
    {
        global $conf;

        $filepath = $_SERVER['DOCUMENT_ROOT'] . $conf['frontend_dir'] . '/' . $filename;

        if (file_exists($filepath)) {
            extract($data);
            include $filepath;
        } else {
            echo "<!-- Файл frontend не найден: " . htmlspecialchars($filename) . " -->";
        }
    }
}

/**
 * @param string $path 
 * @return string 
 */
if (!function_exists('frontend_url')) {
    function frontend_url(string $path): string
    {
        global $conf;
        return $conf['basedir'] . 'frontend/' . $path;
    }
}

// Функция для получения всех пользователей
if (!function_exists('getAllUsers')) {
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
}


// Функция для получения статистики по языкам
if (!function_exists('getLanguageStats')) {
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
}
