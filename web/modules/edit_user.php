<?php
function edit_user_get($request, $user_id) {
    global $db;
    session_start();

    // Проверка авторизации
    if (empty($_SESSION['login'])) {
        return redirect('/login');
    }

    // Проверка прав (админ или собственный профиль)
    $is_admin = check_admin_privileges($db, $_SESSION['login']);
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

        return theme('edit_user', [
            'user' => $user,
            'all_languages' => $all_languages,
            'selected_lang_ids' => $selected_lang_ids,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die("Ошибка сервера");
    }
}

function edit_user_post($request, $user_id) {
    global $db;
    session_start();

    // Проверка CSRF токена
    if (empty($_SESSION['csrf_token']) || 
        !isset($request['post']['csrf_token']) || 
        $request['post']['csrf_token'] !== $_SESSION['csrf_token']) {
        return json_encode(['success' => false, 'message' => 'Неверный CSRF-токен']);
    }

    try {
        $db->beginTransaction();
        
        // Обновление основных данных
        update_user_data($db, $user_id, $request['post']);
        
        // Обновление языков
        update_user_languages($db, $user_id, $request['post']['languages'] ?? []);
        
        $db->commit();
        
        return json_encode([
            'success' => true,
            'message' => 'Данные успешно обновлены'
        ]);
    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Update error: " . $e->getMessage());
        return json_encode(['success' => false, 'message' => 'Ошибка обновления']);
    }
}


include(__DIR__ . '/../theme/edit_user.tpl.php'); 
?>