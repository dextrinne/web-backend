<?php
function edit_user_get($request, $user_id = null) {
    global $db;
    session_start();

    // Если user_id не передан, берем из запроса
    $user_id = $user_id ?? ($_GET['user_id'] ?? $_POST['user_id'] ?? null);
    
    // Проверка авторизации (для обычных пользователей)
    $is_admin = false;
    if (!empty($_SESSION['admin_login'])) {
        try {
            $stmt = $db->prepare("SELECT id FROM admin WHERE login = ?");
            $stmt->execute([$_SESSION['admin_login']]);
            $is_admin = (bool) $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Admin auth error: " . $e->getMessage());
        }
    }

    // Если это не админ и не владелец аккаунта - доступ запрещен
    if (!$is_admin && (empty($_SESSION['uid']) || $_SESSION['uid'] != $user_id)) {
        return access_denied();
    }

    try {
        // Получение данных пользователя
        $stmt = $db->prepare("SELECT * FROM user WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return not_found();
        }

        // Получение выбранных языков пользователя
        $stmt = $db->prepare("
            SELECT l.id, l.name
            FROM language l
            JOIN user_language ul ON l.id = ul.lang_id
            WHERE ul.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $selected_languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $selected_lang_ids = array_column($selected_languages, 'id');

        // Получение всех доступных языков
        $stmt = $db->prepare("SELECT id, name FROM language");
        $stmt->execute();
        $all_languages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Генерация CSRF токена
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Формирование данных для шаблона
        $template_data = [
            'user' => $user,
            'all_languages' => $all_languages,
            'selected_lang_ids' => $selected_lang_ids,
            'csrf_token' => $_SESSION['csrf_token'],
            'is_auth' => true,
            'is_admin' => $is_admin,
            'values' => $user
        ];

        return theme($is_admin ? 'form_reg' : 'form', $template_data);
    } catch (PDOException $e) {
        error_log("Ошибка базы данных: " . $e->getMessage());
        die("Ошибка: Произошла ошибка на сервере.");
    }
}

function edit_user_post($request, $user_id = null) {
    global $db;
    session_start();

    $user_id = $user_id ?? ($request['post']['user_id'] ?? null);
    
    // Проверка CSRF токена
    if (empty($_SESSION['csrf_token']) || 
        !isset($request['post']['csrf_token']) || 
        $request['post']['csrf_token'] !== $_SESSION['csrf_token']) {
        return json_response(['success' => false, 'message' => 'CSRF token validation failed.']);
    }

    // Проверка прав администратора
    if (!isset($_SESSION['admin_login'])) {
        return json_response(['success' => false, 'message' => 'Доступ запрещен.']);
    }

    try {
        // Обновление данных пользователя
        $stmt = $db->prepare("UPDATE user SET fio = ?, tel = ?, email = ?, 
                             bdate = ?, gender = ?, bio = ?, ccheck = ? 
                             WHERE id = ?");
        
        $stmt->execute([
            $request['post']['fio'],
            $request['post']['tel'],
            $request['post']['email'],
            $request['post']['bdate'],
            $request['post']['gender'],
            $request['post']['bio'] ?? '',
            isset($request['post']['ccheck']) ? 1 : 0,
            $user_id
        ]);

        // Обновление языков программирования
        $db->beginTransaction();
        
        // Удаляем старые связи
        $stmt = $db->prepare("DELETE FROM user_language WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Добавляем новые связи
        if (!empty($request['post']['languages']) && is_array($request['post']['languages'])) {
            $stmt = $db->prepare("INSERT INTO user_language (user_id, lang_id) VALUES (?, ?)");
            foreach ($request['post']['languages'] as $lang_id) {
                $stmt->execute([$user_id, intval($lang_id)]);
            }
        }
        
        $db->commit();
        
        return json_response(['success' => true, 'message' => 'Данные успешно обновлены']);

    } catch (PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log("Ошибка при обновлении пользователя: " . $e->getMessage());
        return json_response(['success' => false, 'message' => 'Ошибка при обновлении данных']);
    }
}

function json_response($data) {
    header('Content-Type: application/json');
    die(json_encode($data));
}