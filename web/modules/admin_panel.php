<?php
session_start();
include('./settings.php');
include('./scripts/db.php');
include('./scripts/functions.php');

// Проверка HTTP-авторизации
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Требуется авторизация';
    exit();
}

// Проверка учетных данных администратора
$admin_login = $_SERVER['PHP_AUTH_USER'];
$admin_pass = $_SERVER['PHP_AUTH_PW'];

try {
    // Получаем хеш пароля из базы данных
    $stmt = $db->prepare("SELECT password FROM admin WHERE login = ?");
    $stmt->execute([$admin_login]);
    $admin_data = $stmt->fetch(PDO::FETCH_ASSOC);

   if (!$admin_data) {
        // Администратор с таким логином не найден
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Неверные учетные данные';
        exit();
    }

    // Сравниваем пароли
    if (!password_verify($admin_pass, $admin_data['password'])) {
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Неверные учетные данные';
        exit();
    }
} catch (PDOException $e) {
    error_log('Admin authentication error: ' . $e->getMessage());
    die('Ошибка проверки учетных данных.');
}


// Генерация CSRF-токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Обработка действий администратора
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Проверка CSRF-токена
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['admin_error'] = htmlspecialchars('Неверный CSRF-токен');
        header("Location: /admin");
        exit();
    }

    try {
        if (isset($_POST['delete_user'])) {
            // Удаление пользователя
            $stmt = $db->prepare("DELETE FROM user WHERE id = ?");
            $stmt->execute([intval($_POST['user_id'])]);

        }
    } catch (PDOException $e) {
        error_log('Admin operation error: ' . $e->getMessage());
        $_SESSION['admin_error'] = 'Произошла ошибка при выполнении операции. Пожалуйста, попробуйте позже.';
        header("Location: /admin");
        exit();
    }


    $_SESSION['admin_success'] = 'Изменения успешно сохранены';
    header("Location: /admin");
    exit();
}

// Получение данных для отображения
$users = getAllUsers($db);
$language_stats = getLanguageStats($db);
$all_languages = $db->query("SELECT id, name FROM language")->fetchAll(PDO::FETCH_ASSOC);

echo theme('admin_panel', [
    'users' => $users,
    'language_stats' => $language_stats,
    'all_languages' => $all_languages,
    'csrf_token' => $_SESSION['csrf_token'],
    'admin_error' => $_SESSION['admin_error'] ?? null,
    'admin_success' => $_SESSION['admin_success'] ?? null
]);

unset($_SESSION['admin_error']);
unset($_SESSION['admin_success']);

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
?>
