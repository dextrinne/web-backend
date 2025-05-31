<?php
function form_reg_get($request)
{
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

    // Получение данных формы
    $form_data = get_form_data($db, $is_auth, $uid);
    $errors = $form_data['errors'];
    $values = $form_data['values'];
    $messages = $form_data['messages'];

    // Генерация CSRF токена
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return theme('form_reg', [
        'errors' => $errors,
        'values' => $values,
        'messages' => $messages,
        'is_auth' => $is_auth,
        'is_admin' => $is_admin,
        'csrf_token' => $_SESSION['csrf_token'],
        'conf' => $GLOBALS['conf']
    ]);
}

function form_reg_post($request)
{
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

        if (!empty($_SESSION['login'])) {
            // Пользователь авторизован - обновляем данные
            $user_id = $_SESSION['uid'];

            // 1. Обновляем основную информацию в таблице user
            $stmt = $db->prepare("
                UPDATE user 
                SET 
                    fio = :fio,
                    phone = :phone,
                    email = :email,
                    birthdate = :birthdate,
                    gender = :gender,
                    bio = :bio,
                    agreement = :agreement,
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
                ':agreement' => $validation['values']['agreement'],
                ':user_id' => $user_id
            ]);

            // 2. Обновляем языки программирования в user_language
            $stmt = $db->prepare("DELETE FROM user_language WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $user_id]);

            $stmt = $db->prepare("
                INSERT INTO user_language (user_id, language_id) 
                VALUES (:user_id, :lang_id)
            ");

            foreach ($validation['values']['languages'] as $lang_id) {
                $lang_id = (int) $lang_id;
                if ($lang_id <= 0)
                    continue;

                $stmt->execute([
                    ':user_id' => $user_id,
                    ':lang_id' => $lang_id
                ]);
            }

            $db->commit();

            return json_response([
                'success' => true,
                'message' => 'Ваши данные успешно обновлены',
                'redirect' => '/'
            ]);
        } else {
            // Новый пользователь - создаем запись
            $login = 'user_' . bin2hex(random_bytes(4));
            $password = substr(bin2hex(random_bytes(8)), 0, 8);
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // 1. Создаем запись в user_login
            $stmt = $db->prepare("
                INSERT INTO user_login (login, password, created_at) 
                VALUES (:login, :password, NOW())
            ");
            $stmt->execute([
                ':login' => $login,
                ':password' => $password_hash
            ]);
            $login_id = $db->lastInsertId();

            // 2. Создаем запись в user
            $stmt = $db->prepare("
                INSERT INTO user (
                    login_id, fio, phone, email, birthdate, 
                    gender, bio, agreement, created_at
                ) VALUES (
                    :login_id, :fio, :phone, :email, :birthdate,
                    :gender, :bio, :agreement, NOW()
                )
            ");
            $stmt->execute([
                ':login_id' => $login_id,
                ':fio' => $validation['values']['fio'],
                ':phone' => $validation['values']['phone'],
                ':email' => $validation['values']['email'],
                ':birthdate' => $validation['values']['birthdate'],
                ':gender' => $validation['values']['gender'],
                ':bio' => $validation['values']['bio'],
                ':agreement' => $validation['values']['agreement']
            ]);
            $user_id = $db->lastInsertId();

            // 3. Добавляем языки в user_language
            $stmt = $db->prepare("
                INSERT INTO user_language (user_id, language_id)
                VALUES (:user_id, :lang_id)
            ");
            foreach ($validation['values']['languages'] as $lang_id) {
                $lang_id = (int) $lang_id;
                if ($lang_id <= 0)
                    continue;

                $stmt->execute([
                    ':user_id' => $user_id,
                    ':lang_id' => $lang_id
                ]);
            }

            $db->commit();

            return json_response([
                'success' => true,
                'message' => 'Регистрация успешно завершена',
                'credentials' => [
                    'login' => $login,
                    'password' => $password
                ],
                'redirect' => '/'
            ]);
        }
    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Database error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        return json_response([
            'success' => false,
            'errors' => ['general' => 'Ошибка базы данных: ' . $e->getMessage()]
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Error: " . $e->getMessage());
        return json_response([
            'success' => false,
            'errors' => ['general' => 'Ошибка обработки данных']
        ]);
    }
}

// Вспомогательные функции
function connect_db()
{
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

function check_auth($db)
{
    $result = ['is_auth' => false, 'is_admin' => false, 'uid' => null];

    if (!empty($_SESSION['login'])) {
        try {
            // Проверяем user_login и связь с admin
            $stmt = $db->prepare("
                SELECT u.id, a.id AS admin_id 
                FROM user_login ul
                JOIN user u ON ul.id = u.login_id
                LEFT JOIN admin a ON u.id = a.user_id
                WHERE ul.login = ?
            ");
            $stmt->execute([$_SESSION['login']]);
            $user = $stmt->fetch();

            if ($user) {
                $result = [
                    'is_auth' => true,
                    'is_admin' => !empty($user['admin_id']),
                    'uid' => $user['id']
                ];
            }
        } catch (PDOException $e) {
            error_log("Auth Check Error: " . $e->getMessage());
        }
    }

    return $result;
}

function get_form_data($db, $is_auth, $uid)
{
    $data = [
        'errors' => $_SESSION['form_errors'] ?? [],
        'values' => $_SESSION['form_values'] ?? [],
        'messages' => $_SESSION['form_messages'] ?? []
    ];

    if ($is_auth && $uid) {
        try {
            // Получаем данные из таблицы user
            $stmt = $db->prepare("SELECT * FROM user WHERE id = ?");
            $stmt->execute([$uid]);
            $user_data = $stmt->fetch();

            if ($user_data) {
                $data['values'] = array_merge($data['values'], [
                    'fio' => htmlspecialchars($user_data['fio'] ?? ''),
                    'phone' => htmlspecialchars($user_data['phone'] ?? ''),
                    'email' => htmlspecialchars($user_data['email'] ?? ''),
                    'birthdate' => htmlspecialchars($user_data['birthdate'] ?? ''),
                    'gender' => htmlspecialchars($user_data['gender'] ?? ''),
                    'bio' => htmlspecialchars($user_data['bio'] ?? ''),
                    'agreement' => $user_data['agreement'] ?? 0
                ]);

                // Получаем выбранные языки из user_language
                $stmt = $db->prepare("
                    SELECT language_id 
                    FROM user_language 
                    WHERE user_id = ?
                ");
                $stmt->execute([$uid]);
                $data['values']['languages'] = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            }
        } catch (PDOException $e) {
            error_log("Form Data Load Error: " . $e->getMessage());
            $data['messages'][] = "Ошибка загрузки данных";
        }
    }

    unset($_SESSION['form_errors'], $_SESSION['form_values'], $_SESSION['form_messages']);
    return $data;
}

function validate_csrf($request)
{
    return !empty($_SESSION['csrf_token']) &&
        !empty($request['post']['csrf_token']) &&
        $request['post']['csrf_token'] === $_SESSION['csrf_token'];
}

function validate_form_data($post_data)
{
    $values = [];
    $errors = [];

    // 1. ФИО
    $values['fio'] = trim($post_data['fio'] ?? '');
    if (empty($values['fio'])) {
        $errors['fio'] = 'ФИО обязательно для заполнения';
    } elseif (!preg_match("/^[а-яА-ЯёЁa-zA-Z\s\-]+$/u", $values['fio'])) {
        $errors['fio'] = 'ФИО должно содержать только буквы, пробелы и дефисы';
    } elseif (strlen($values['fio']) > 200) {
        $errors['fio'] = 'ФИО не должно превышать 200 символов';
    }

    // 2. Телефон
    $values['phone'] = trim($post_data['phone'] ?? '');
    if (empty($values['phone'])) {
        $errors['phone'] = 'Телефон обязателен для заполнения';
    } elseif (!preg_match("/^\+7\s?\(?\d{3}\)?\s?\d{3}-?\d{2}-?\d{2}$/", $values['phone'])) {
        $errors['phone'] = 'Введите телефон в формате +7 (XXX) XXX-XX-XX';
    }

    // 3. Email
    $values['email'] = trim($post_data['email'] ?? '');
    if (empty($values['email'])) {
        $errors['email'] = 'Email обязателен для заполнения';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный email адрес';
    } elseif (strlen($values['email']) > 100) {
        $errors['email'] = 'Email не должен превышать 100 символов';
    }

    // 4. Дата рождения
    $values['birthdate'] = trim($post_data['birthdate'] ?? '');
    if (empty($values['birthdate'])) {
        $errors['birthdate'] = 'Дата рождения обязательна';
    } else {
        try {
            $birthdate = new DateTime($values['birthdate']);
            $now = new DateTime();
            $age = $now->diff($birthdate)->y;

            if ($age < 18) {
                $errors['birthdate'] = 'Возраст должен быть 18+ лет';
            } elseif ($age > 120) {
                $errors['birthdate'] = 'Проверьте дату рождения';
            }
        } catch (Exception $e) {
            $errors['birthdate'] = 'Некорректный формат даты (используйте YYYY-MM-DD)';
        }
    }

    // 5. Пол
    $values['gender'] = $post_data['gender'] ?? '';
    if (empty($values['gender'])) {
        $errors['gender'] = 'Укажите пол';
    } elseif (!in_array($values['gender'], ['male', 'female'])) {
        $errors['gender'] = 'Выберите пол из предложенных вариантов';
    }

    // 6. Языки программирования
    $values['languages'] = $post_data['languages'] ?? [];
    if (empty($values['languages'])) {
        $errors['languages'] = 'Выберите хотя бы один язык';
    } else {
        foreach ($values['languages'] as $lang_id) {
            if (!is_numeric($lang_id)) {
                $errors['languages'] = 'Некорректный выбор языков';
                break;
            }
        }
    }

    // 7. Биография
    $values['bio'] = trim($post_data['bio'] ?? '');
    if (!empty($values['bio']) && strlen($values['bio']) > 1000) {
        $errors['bio'] = 'Биография не должна превышать 1000 символов';
    }

    // 8. Соглашение
    $values['agreement'] = isset($post_data['agreement']) ? 1 : 0;
    if (!$values['agreement']) {
        $errors['agreement'] = 'Необходимо принять соглашение';
    }

    return ['values' => $values, 'errors' => $errors];
}

function json_response($data)
{
    header('Content-Type: application/json');
    die(json_encode($data));
}