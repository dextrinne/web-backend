<?php
session_start();

// Обработка запроса на редактирование от администратора
if (isset($_GET['admin_edit']) && $_GET['admin_edit'] == 1) {
    // Проверяем авторизацию администратора через сессию
    if (empty($_SESSION['admin_login'])) {
        $_SESSION['admin_redirect'] = $_SERVER['REQUEST_URI'];
        header("Location: /web-backend/web/modules/admin_panel.php");
        exit();
    }
    
    // Сохраняем ID пользователя для редактирования
    $_SESSION['edit_user_id'] = (int)$_GET['user_id'];
    header("Location: /web-backend/web/#form-anchor");
    exit();
}

ob_start();
include('./settings.php');
ini_set('display_errors', DISPLAY_ERRORS);
ini_set('include_path', INCLUDE_PATH);

include('./scripts/db.php');  
include_once('./scripts/functions.php');
include_once('./scripts/init.php');

$request = array(
    'url' => isset($_GET['q']) ? $_GET['q'] : '',
    'method' => $_SERVER['REQUEST_METHOD'] === 'POST' ? 'post' : 'get',
    'get' => !empty($_GET) ? $_GET : array(),
    'post' => $_POST,
    'put' => !empty($_POST) && !empty($_POST['method']) && $_POST['method'] == 'put' ? $_POST : array(),
    'delete' => !empty($_POST) && !empty($_POST['method']) && $_POST['method'] == 'put' ? $_POST : array(),
    'Content-Type' => 'text/html',
);

$response = init($request, $urlconf, $db);

/*if (!empty($response['headers'])) {
    foreach ($response['headers'] as $key => $value) {
        if (is_string($key)) {
            header(sprintf('%s: %s', $key, $value));
        } else {
            header($value);
        }
    }
}
if (!empty($response['entity'])) {
    print ($response['entity']);
}*/

// Если есть вывод или это админка - выводим как есть
if (ob_get_length() > 0 || $request['url'] === 'admin') {
    ob_end_flush();
} else {
    ob_end_clean();
    if (!empty($response['headers'])) {
        foreach ($response['headers'] as $key => $value) {
            header(sprintf('%s: %s', $key, $value));
        }
    }
    if (!empty($response['entity'])) {
        print $response['entity'];
    } else {
        $response = not_found();
        foreach ($response['headers'] as $key => $value) {
            header(sprintf('%s: %s', $key, $value));
        }
        print $response['entity'];
    }
}