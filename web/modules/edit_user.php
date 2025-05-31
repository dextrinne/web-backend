<?php
header('Content-Type: text/html; charset=UTF-8');
include_once(__DIR__ . '/../scripts/db.php');
include(__DIR__ . '/../scripts/functions.php');

function edit_user_get($request, $user_id) {
    global $db;

    session_start();

    // Проверка авторизации пользователя
    if (empty($_SESSION['login'])) {
        return redirect('/login');
    }

    // Проверка прав администратора
    $is_admin = check_admin_privileges($db, $_SESSION['login']);

    // Проверка, что пользователь редактирует свои данные или является администратором
    if (!$is_admin && $_SESSION['uid'] != $user_id) {
        return access_denied();
    }

    try {
        // Получение данных пользователя
        $user = get_user_data($db, $user_id);
        if (!$user) {
            return not_found();
        }

        // Получение языков программирования
        $all_languages = get_all_languages($db);
        $selected_lang_ids = get_user_languages($db, $user_id);

        // Генерация CSRF токена
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Подготовка данных для шаблона
        $template_data = [
            'user' => $user,
            'all_languages' => $all_languages,
            'selected_lang_ids' => $selected_lang_ids,
            'csrf_token' => $_SESSION['csrf_token'],
        ];

        return theme('edit_user', ['#content' => $template_data]);

    } catch (PDOException $e) {
        error_log("Database error in edit_user_get: " . $e->getMessage());
        die("Ошибка: Произошла ошибка на сервере.");
    }
}

function edit_user_post($request, $user_id) {
    global $db;

    session_start();

    // Проверка CSRF токена
    if (empty($_SESSION['csrf_token']) || 
        !isset($request['post']['csrf_token']) || 
        $request['post']['csrf_token'] !== $_SESSION['csrf_token']) {
        return json_response(['success' => false, 'message' => 'Неверный CSRF-токен']);
    }

    try {
        $db->beginTransaction();

        // Обновление основных данных пользователя
        update_user_data($db, $user_id, $request['post']);

        // Обновление языков программирования
        update_user_languages($db, $user_id, $request['post']['languages'] ?? []);

        $db->commit();

        return json_response(['success' => true, 'message' => 'Данные успешно обновлены']);

    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Error in edit_user_post: " . $e->getMessage());
        return json_response(['success' => false, 'message' => 'Ошибка при обновлении данных']);
    }
}

function json_response($data) {
    header('Content-Type: application/json');
    die(json_encode($data));
}

//include(__DIR__ . '/../theme/edit_user.tpl.php'); 
?>