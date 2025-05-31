<?php
// Проверяем, не запущена ли уже сессия
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function form_reg_get($request) {
    global $db;

    $is_auth = !empty($_SESSION['login']) && !empty($_SESSION['uid']);
    $is_admin = false;
    $uid = $_SESSION['uid'] ?? null;

    // Форма на главной странице ВСЕГДА будет регистрацией
    $form_type = (isset($request['is_home']) && $request['is_home']) ? 'register' : 'update';

    // Если пользователь не авторизован, форма всегда регистрация
    if (!$is_auth) {
        $form_type = 'register';
    }

    // Получаем данные пользователя только для формы редактирования
    $values = [];
    $selected_lang_ids = [];
    if ($form_type === 'update' && $uid) {
        try {
            $stmt = $db->prepare("SELECT * FROM user WHERE id = ?");
            $stmt->execute([$uid]);
            $values = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $db->prepare("SELECT lang_id FROM user_language WHERE user_id = ?");
            $stmt->execute([$uid]);
            $selected_lang_ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (PDOException $e) {
            die('Ошибка загрузки данных: ' . $e->getMessage());
        }
    }

    // Генерация CSRF токена
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return theme('form_reg', [
        'is_auth' => $is_auth,
        'is_admin' => $is_admin,
        'values' => $values,
        'all_languages' => $db->query("SELECT id, name FROM language")->fetchAll(),
        'selected_lang_ids' => $selected_lang_ids,
        'csrf_token' => $_SESSION['csrf_token'],
        'errors' => $_SESSION['form_errors'] ?? [],
        'form_type' => $form_type
    ]);
}

function form_reg_post($request) {
    // Проверка статуса сессии
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

    // Проверка CSRF токена
    if (empty($_SESSION['csrf_token']) || empty($request['post']['csrf_token']) || 
        $request['post']['csrf_token'] !== $_SESSION['csrf_token']) {
        return json_response([
            'success' => false,
            'errors' => ['general' => 'Недействительный CSRF токен']
        ]);
    }

    // Валидация данных
    $validation = validate_form_data($request['post']);
    if (!empty($validation['errors'])) {
        return json_response([
            'success' => false,
            'errors' => $validation['errors']
        ]);
    }

    try {
        $db->beginTransaction();

        // Проверяем, авторизован ли пользователь
        $is_auth = !empty($_SESSION['login']) && !empty($_SESSION['uid']);

        if ($is_auth) {
            // Обновление данных существующего пользователя
            $user_id = $_SESSION['uid'];
            
            // Обновляем основную информацию
            $stmt = $db->prepare("
                UPDATE user 
                SET fio = ?, tel = ?, email = ?, bdate = ?, gender = ?, bio = ?, ccheck = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $validation['values']['fio'],
                $validation['values']['tel'],
                $validation['values']['email'],
                $validation['values']['bdate'],
                $validation['values']['gender'],
                $validation['values']['bio'],
                $validation['values']['ccheck'] ? 1 : 0,
                $user_id
            ]);

            // Обновляем языки программирования
            $stmt = $db->prepare("DELETE FROM user_language WHERE user_id = ?");
            $stmt->execute([$user_id]);

            if (!empty($validation['values']['languages'])) {
                $stmt = $db->prepare("INSERT INTO user_language (user_id, lang_id) VALUES (?, ?)");
                foreach ($validation['values']['languages'] as $lang_id) {
                    $stmt->execute([$user_id, (int)$lang_id]);
                }
            }

            $message = 'Данные успешно обновлены';
        } else {
            // Регистрация нового пользователя
            try {
                $db->beginTransaction();

                // 1. Сначала вставляем данные в таблицу `user`
                $stmt = $db->prepare("
                    INSERT INTO user (fio, tel, email, bdate, gender, bio, ccheck)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $validation['values']['fio'],
                    $validation['values']['tel'],
                    $validation['values']['email'],
                    $validation['values']['bdate'],
                    $validation['values']['gender'],
                    $validation['values']['bio'],
                    $validation['values']['ccheck'] ? 1 : 0
                ]);
                $user_id = $db->lastInsertId(); // Получаем ID нового пользователя

                // 2. Теперь вставляем запись в `user_login` с полученным user_id
                $login = 'user_' . bin2hex(random_bytes(4));
                $password = substr(bin2hex(random_bytes(8)), 0, 8);
                $password_hash = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $db->prepare("
                    INSERT INTO user_login (user_id, login, password, role)
                    VALUES (?, ?, ?, 'user')
                ");
                $stmt->execute([$user_id, $login, $password_hash]);

                // 3. Добавляем выбранные языки
                if (!empty($validation['values']['languages'])) {
                    $stmt = $db->prepare("
                        INSERT INTO user_language (user_id, lang_id)
                        VALUES (?, ?)
                    ");
                    foreach ($validation['values']['languages'] as $lang_id) {
                        $stmt->execute([$user_id, (int)$lang_id]);
                    }
                }

                $db->commit();

                // Сохраняем логин и пароль для показа пользователю
                $_SESSION['new_login'] = $login;
                $_SESSION['new_password'] = $password;

                return json_response([
                    'success' => true,
                    'message' => 'Регистрация успешно завершена',
                    'redirect' => '/web-backend/web/success.php'
                ]);

            } catch (PDOException $e) {
                $db->rollBack();
                return json_response([
                    'success' => false,
                    'errors' => ['general' => 'Ошибка базы данных: ' . $e->getMessage()]
                ]);
            }
}

function validate_form_data($post_data) {
    $values = [];
    $errors = [];

    // ФИО
    $values['fio'] = trim($post_data['fio'] ?? '');
    if (empty($values['fio'])) {
        $errors['fio'] = 'ФИО обязательно для заполнения';
    } elseif (strlen($values['fio']) > 150) {
        $errors['fio'] = 'ФИО не должно превышать 150 символов';
    }

    // Телефон
    $values['tel'] = trim($post_data['tel'] ?? '');
    if (empty($values['tel'])) {
        $errors['tel'] = 'Телефон обязателен для заполнения';
    } elseif (!preg_match('/^\+7\s?\(?\d{3}\)?\s?\d{3}-?\d{2}-?\d{2}$/', $values['tel'])) {
        $errors['tel'] = 'Введите телефон в формате +7 (XXX) XXX-XX-XX';
    }

    // Email
    $values['email'] = trim($post_data['email'] ?? '');
    if (empty($values['email'])) {
        $errors['email'] = 'Email обязателен для заполнения';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный email адрес';
    } elseif (strlen($values['email']) > 255) {
        $errors['email'] = 'Email не должен превышать 255 символов';
    }

    // Дата рождения
    $values['bdate'] = $post_data['bdate'] ?? '';
    if (empty($values['bdate'])) {
        $errors['bdate'] = 'Дата рождения обязательна';
    } else {
        try {
            $birthdate = new DateTime($values['bdate']);
            $now = new DateTime();
            $age = $now->diff($birthdate)->y;
            
            if ($age < 18) {
                $errors['bdate'] = 'Возраст должен быть 18+ лет';
            } elseif ($age > 120) {
                $errors['bdate'] = 'Проверьте дату рождения';
            }
        } catch (Exception $e) {
            $errors['bdate'] = 'Некорректный формат даты';
        }
    }

    // Пол
    $values['gender'] = $post_data['gender'] ?? '';
    if (empty($values['gender'])) {
        $errors['gender'] = 'Укажите пол';
    } elseif (!in_array($values['gender'], ['Male', 'Female'])) {
        $errors['gender'] = 'Выберите пол из предложенных вариантов';
    }

    // Языки программирования
    $values['languages'] = $post_data['languages'] ?? [];
    if (empty($values['languages'])) {
        $errors['languages'] = 'Выберите хотя бы один язык';
    }

    // Биография
    $values['bio'] = trim($post_data['bio'] ?? '');
    if (!empty($values['bio']) && strlen($values['bio']) > 5000) {
        $errors['bio'] = 'Биография не должна превышать 5000 символов';
    }

    // Соглашение
    $values['ccheck'] = isset($post_data['ccheck']) ? 1 : 0;
    if (!$values['ccheck']) {
        $errors['ccheck'] = 'Необходимо принять соглашение';
    }

    return ['values' => $values, 'errors' => $errors];
}

function connect_db() {
    try {
        return new PDO(
            'mysql:host=localhost;dbname=u68595;charset=utf8',
            'u68595',
            '6788124',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    } catch (PDOException $e) {
        error_log("DB Connection Error: " . $e->getMessage());
        return false;
    }
}

function json_response($data) {
    header('Content-Type: application/json');
    die(json_encode($data));
}
?>