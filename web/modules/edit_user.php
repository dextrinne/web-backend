<?php
function edit_user_get($request) {
    // Инициализация сессии
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Подключение к базе данных
    $db = connect_db();
    if (!$db) {
        die("Ошибка подключения к базе данных");
    }

    // Проверка авторизации и прав
    $auth_data = check_auth($db);
    $is_auth = $auth_data['is_auth'];
    $is_admin = $auth_data['is_admin'];
    $uid = $auth_data['uid'];

    // Получаем ID пользователя для редактирования
    $edit_user_id = isset($request['params'][0]) ? (int)$request['params'][0] : $uid;

    // Проверка прав доступа (админ или владелец аккаунта)
    if (!$is_admin && $edit_user_id !== $uid) {
        return access_denied();
    }

    // Получаем данные пользователя
    $form_data = get_form_data($db, true, $edit_user_id);

    // Генерация CSRF токена
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Подключаем шаблон формы регистрации в режиме редактирования
    return theme('form_reg', [
        'errors' => $form_data['errors'],
        'values' => $form_data['values'],
        'messages' => $form_data['messages'],
        'is_auth' => $is_auth,
        'is_admin' => $is_admin,
        'csrf_token' => $_SESSION['csrf_token'],
        'conf' => $GLOBALS['conf'],
        'is_edit_mode' => true, // Флаг режима редактирования
        'edit_user_id' => $edit_user_id // ID редактируемого пользователя
    ]);
}

function edit_user_post($request) {
    // Инициализация сессии
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Подключение к базе данных
    $db = connect_db();
    if (!$db) {
        return json_response([
            'success' => false,
            'errors' => ['general' => 'Ошибка подключения к базе данных']
        ]);
    }

    // Проверка авторизации и прав
    $auth_data = check_auth($db);
    $is_auth = $auth_data['is_auth'];
    $uid = $auth_data['uid'];

    // Получаем ID пользователя из URL или сессии
    $edit_user_id = isset($request['params'][0]) ? (int)$request['params'][0] : $uid;

    // Проверка CSRF токена
    if (!validate_csrf($request)) {
        return json_response([
            'success' => false,
            'errors' => ['general' => 'Недействительный CSRF токен']
        ]);
    }

    // Валидация данных формы
    $validation = validate_form_data($request['post']);
    if (!empty($validation['errors'])) {
        return json_response([
            'success' => false,
            'errors' => $validation['errors']
        ]);
    }

    try {
        $db->beginTransaction();

        // Обновляем основную информацию в таблице user
        $stmt = $db->prepare("
            UPDATE user
            SET
                fio = :fio,
                tel = :phone,
                email = :email,
                bdate = :birthdate,
                gender = :gender,
                bio = :bio,
                ccheck = :agreement,
                updated_at = NOW()
            WHERE id = :user_id
        ");
        $stmt->execute([
            ':fio' => $validation['values']['fio'],
            ':phone' => $validation['values']['phone'],
            ':email' => $validation['values']['email'],
            ':birthdate' => $validation['values']['birthdate'],
            ':gender' => $validation['values']['gender'],
            ':bio' => $validation['values']['bio'],
            ':agreement' => $validation['values']['agreement'] ? 1 : 0,
            ':user_id' => $edit_user_id
        ]);

        // Обновляем языки программирования
        $stmt = $db->prepare("DELETE FROM user_language WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $edit_user_id]);

        $stmt = $db->prepare("
            INSERT INTO user_language (user_id, lang_id)
            VALUES (:user_id, :lang_id)
        ");
        foreach ($validation['values']['languages'] as $lang_id) {
            $lang_id = (int)$lang_id;
            if ($lang_id <= 0) continue;
            $stmt->execute([
                ':user_id' => $edit_user_id,
                ':lang_id' => $lang_id
            ]);
        }

        $db->commit();

        return json_response([
            'success' => true,
            'message' => 'Данные успешно обновлены',
            'redirect' => $is_auth ? '/' : 'login.php'
        ]);

    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Database error in edit_user_post: " . $e->getMessage());
        return json_response([
            'success' => false,
            'errors' => ['general' => 'Ошибка базы данных: ' . $e->getMessage()]
        ]);
    }
}