<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

include_once(__DIR__ . '/../scripts/db.php');
include(__DIR__ . '/../scripts/functions.php');

// Проверка авторизации пользователя или администратора
if (empty($_SESSION['login'])) {
    header('Location: login.php');
    exit();
}

// Получаем ID пользователя для редактирования
$user_id = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['uid'];

// Проверяем, имеет ли текущий пользователь права на редактирование
if (!$_SESSION['is_admin'] && $user_id != $_SESSION['uid']) {
    header('Location: 403.php');
    exit();
}

// Получаем данные пользователя
try {
    $stmt = $db->prepare("
        SELECT u.*, GROUP_CONCAT(ul.lang_id) as languages 
        FROM user u
        LEFT JOIN user_language ul ON u.id = ul.user_id
        WHERE u.id = ?
        GROUP BY u.id
    ");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        header('Location: 404.php');
        exit();
    }
} catch (PDOException $e) {
    die('Ошибка получения данных пользователя: ' . $e->getMessage());
}

// Генерация CSRF-токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Проверка CSRF-токена
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['form_errors'] = ['general' => 'Неверный CSRF-токен'];
        header("Location: edit_user.php?id=$user_id");
        exit();
    }

    // Валидация данных
    $validation = validate_form_data($_POST);
    
    if (!empty($validation['errors'])) {
        $_SESSION['form_errors'] = $validation['errors'];
        $_SESSION['form_values'] = $validation['values'];
        header("Location: edit_user.php?id=$user_id");
        exit();
    }

    // Обновление данных в базе
    try {
        $db->beginTransaction();

        // Обновляем основную информацию
        $stmt = $db->prepare("
            UPDATE user 
            SET fio = ?, tel = ?, email = ?, bdate = ?, gender = ?, bio = ?, ccheck = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $validation['values']['fio'],
            $validation['values']['phone'],
            $validation['values']['email'],
            $validation['values']['birthdate'],
            $validation['values']['gender'],
            $validation['values']['bio'],
            $validation['values']['agreement'] ? 1 : 0,
            $user_id
        ]);

        // Обновляем языки программирования
        $stmt = $db->prepare("DELETE FROM user_language WHERE user_id = ?");
        $stmt->execute([$user_id]);

        $stmt = $db->prepare("INSERT INTO user_language (user_id, lang_id) VALUES (?, ?)");
        foreach ($validation['values']['languages'] as $lang_id) {
            $stmt->execute([$user_id, (int)$lang_id]);
        }

        $db->commit();
        
        $_SESSION['form_messages'] = ['Данные успешно обновлены'];
        header("Location: edit_user.php?id=$user_id");
        exit();
    } catch (PDOException $e) {
        $db->rollBack();
        $_SESSION['form_errors'] = ['general' => 'Ошибка базы данных: ' . $e->getMessage()];
        header("Location: edit_user.php?id=$user_id");
        exit();
    }
}

// Получаем список всех языков
$languages = [];
try {
    $stmt = $db->prepare("SELECT id, name FROM language");
    $stmt->execute();
    $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Ошибка получения списка языков: ' . $e->getMessage());
}

// Подготовка данных для шаблона
$values = [
    'fio' => $user_data['fio'],
    'phone' => $user_data['tel'],
    'email' => $user_data['email'],
    'birthdate' => $user_data['bdate'],
    'gender' => strtolower($user_data['gender']),
    'bio' => $user_data['bio'],
    'agreement' => $user_data['ccheck'],
    'languages' => $user_data['languages'] ? explode(',', $user_data['languages']) : []
];

$errors = $_SESSION['form_errors'] ?? [];
$messages = $_SESSION['form_messages'] ?? [];

unset($_SESSION['form_errors'], $_SESSION['form_messages']);

// Подключение шаблона
include(__DIR__ . '/../theme/edit_user.tpl.php');
?>