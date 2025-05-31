<?php
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

// Вспомогательные функции

function check_admin_privileges($db, $login) {
    try {
        $stmt = $db->prepare("SELECT id FROM admin WHERE login = ?");
        $stmt->execute([$login]);
        return (bool) $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Admin auth error: " . $e->getMessage());
        return false;
    }
}

function get_user_data($db, $user_id) {
    $stmt = $db->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_all_languages($db) {
    $stmt = $db->prepare("SELECT id, name FROM language");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_user_languages($db, $user_id) {
    $stmt = $db->prepare("
        SELECT l.id 
        FROM language l
        JOIN user_language ul ON l.id = ul.lang_id
        WHERE ul.user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
}

function update_user_data($db, $user_id, $post_data) {
    $stmt = $db->prepare("
        UPDATE user
        SET fio = ?, tel = ?, email = ?, bdate = ?, gender = ?, bio = ?, ccheck = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        htmlspecialchars($post_data['fio']),
        htmlspecialchars($post_data['tel']),
        htmlspecialchars($post_data['email']),
        $post_data['bdate'],
        $post_data['gender'],
        htmlspecialchars($post_data['bio']),
        isset($post_data['ccheck']) ? 1 : 0,
        $user_id
    ]);
}

function update_user_languages($db, $user_id, $languages) {
    // Удаляем старые связи
    $stmt = $db->prepare("DELETE FROM user_language WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Добавляем новые связи
    if (!empty($languages)) {
        $stmt = $db->prepare("INSERT INTO user_language (user_id, lang_id) VALUES (?, ?)");
        foreach ($languages as $lang_id) {
            $stmt->execute([$user_id, intval($lang_id)]);
        }
    }
}

function json_response($data) {
    header('Content-Type: application/json');
    die(json_encode($data));
}

include(__DIR__ . '/../theme/edit_user.tpl.php'); 
?>