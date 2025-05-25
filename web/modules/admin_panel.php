<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();
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

    // Сравниваем хеши паролей
    $hashed_input = hash('sha256', $admin_pass);
    if ($hashed_input !== $admin_data['password']) {
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
        header("Location: admin.php");
        exit();
    }

    try {
        if (isset($_POST['delete_user'])) {
            // Удаление пользователя
            $stmt = $db->prepare("DELETE FROM user WHERE id = ?");
            $stmt->execute([intval($_POST['user_id'])]);
            $_SESSION['admin_success'] = 'Пользователь успешно удален';
        }
    } catch (PDOException $e) {
        error_log('Admin operation error: ' . $e->getMessage());
        $_SESSION['admin_error'] = 'Произошла ошибка при выполнении операции. Пожалуйста, попробуйте позже.';
    }

    header("Location: admin.php");
    exit();
}

// Получение данных для отображения
$users = getAllUsers($db);
$language_stats = getLanguageStats($db);

// Подключение шаблона
include('./theme/admin_panel.tpl.php');
?>