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
        'csrf_token' => $_SESSION['csrf_token']
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

            // 1. Обновляем основную информацию в таблице applications
            $stmt = $db->prepare("
                UPDATE applications 
                SET 
                    fio = :fio,
                    phone = :phone,
                    email = :email,
                    birthdate = :birthdate,
                    gender = :gender,
                    bio = :bio,
                    agreement = :agreement,
                    updated_at = NOW()
                WHERE user_id = :user_id
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

            // 2. Получаем ID связанной заявки
            $stmt = $db->prepare("SELECT id FROM applications WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $user_id]);
            $app_id = $stmt->fetchColumn();

            // 3. Обновляем языки программирования
            // Сначала удаляем все существующие связи
            $stmt = $db->prepare("DELETE FROM application_languages WHERE application_id = :app_id");
            $stmt->execute([':app_id' => $app_id]);

            // Затем добавляем новые
            $stmt = $db->prepare("
                INSERT INTO application_languages (application_id, language_id) 
                VALUES (:app_id, :lang_id)
            ");

            foreach ($validation['values']['languages'] as $lang_id) {
                $lang_id = (int) $lang_id;
                if ($lang_id <= 0)
                    continue;

                $stmt->execute([
                    ':app_id' => $app_id,
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
            // 1. Генерируем уникальные учетные данные
            $login = 'user_' . bin2hex(random_bytes(4));
            $password = substr(bin2hex(random_bytes(8)), 0, 8);
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // 2. Создаем запись в таблице users
            $stmt = $db->prepare("
                INSERT INTO users (login, password, is_admin, created_at) 
                VALUES (:login, :password, 0, NOW())
            ");

            $stmt->execute([
                ':login' => $login,
                ':password' => $password_hash
            ]);

            $user_id = $db->lastInsertId();

            // 3. Создаем связанную запись в таблице applications
            $stmt = $db->prepare("
                INSERT INTO applications (
                    user_id, fio, phone, email, birthdate, 
                    gender, bio, agreement, created_at
                ) VALUES (
                    :user_id, :fio, :phone, :email, :birthdate,
                    :gender, :bio, :agreement, NOW()
                )
            ");

            $stmt->execute([
                ':user_id' => $user_id,
                ':fio' => $validation['values']['fio'],
                ':phone' => $validation['values']['phone'],
                ':email' => $validation['values']['email'],
                ':birthdate' => $validation['values']['birthdate'],
                ':gender' => $validation['values']['gender'],
                ':bio' => $validation['values']['bio'],
                ':agreement' => $validation['values']['agreement']
            ]);

            $app_id = $db->lastInsertId();

            // 4. Добавляем выбранные языки программирования
            $stmt = $db->prepare("
                INSERT INTO application_languages (application_id, language_id)
                VALUES (:app_id, :lang_id)
            ");

            foreach ($validation['values']['languages'] as $lang_id) {
                $lang_id = (int) $lang_id;
                if ($lang_id <= 0)
                    continue;

                $stmt->execute([
                    ':app_id' => $app_id,
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
        error_log("Database error in form processing: " . $e->getMessage());
        return json_response([
            'success' => false,
            'errors' => ['general' => 'Ошибка базы данных: ' . $e->getMessage()]
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Error in form processing: " . $e->getMessage());
        return json_response([
            'success' => false,
            'errors' => ['general' => 'Ошибка обработки данных']
        ]);
    }
}
// ===== ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ =====

function connect_db()
{
    try {
        return new PDO(
            'mysql:host=localhost;dbname=u68761;charset=utf8',
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
            $stmt = $db->prepare("SELECT id, is_admin FROM users WHERE login = ?");
            $stmt->execute([$_SESSION['login']]);
            $user = $stmt->fetch();

            if ($user) {
                $result = [
                    'is_auth' => true,
                    'is_admin' => (bool) $user['is_admin'],
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
            $stmt = $db->prepare("SELECT * FROM applications WHERE user_id = ?");
            $stmt->execute([$uid]);
            $app_data = $stmt->fetch();

            if ($app_data) {
                $data['values'] = array_merge($data['values'], [
                    'fio' => htmlspecialchars($app_data['fio'] ?? ''),
                    'phone' => htmlspecialchars($app_data['phone'] ?? ''),
                    'email' => htmlspecialchars($app_data['email'] ?? ''),
                    'birthdate' => htmlspecialchars($app_data['birthdate'] ?? ''),
                    'gender' => htmlspecialchars($app_data['gender'] ?? ''),
                    'bio' => htmlspecialchars($app_data['bio'] ?? ''),
                    'agreement' => $app_data['agreement'] ?? 0
                ]);

                $stmt = $db->prepare("SELECT language_id FROM application_languages WHERE application_id = ?");
                $stmt->execute([$app_data['id']]);
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

    // 1. Валидация ФИО
    $values['fio'] = trim($post_data['fio'] ?? '');
    if (empty($values['fio'])) {
        $errors['fio'] = 'ФИО обязательно для заполнения';
    } elseif (!preg_match("/^[а-яА-ЯёЁa-zA-Z\s\-]+$/u", $values['fio'])) {
        $errors['fio'] = 'ФИО должно содержать только буквы, пробелы и дефисы';
    } elseif (mb_strlen($values['fio']) > 200) {
        $errors['fio'] = 'ФИО не должно превышать 200 символов';
    }

    // 2. Валидация телефона
    $values['phone'] = trim($post_data['phone'] ?? '');
    if (empty($values['phone'])) {
        $errors['phone'] = 'Телефон обязателен для заполнения';
    } elseif (!preg_match("/^\+7\s?\(?\d{3}\)?\s?\d{3}-?\d{2}-?\d{2}$/", $values['phone'])) {
        $errors['phone'] = 'Введите телефон в формате +7 (XXX) XXX-XX-XX';
    }

    // 3. Валидация email
    $values['email'] = trim($post_data['email'] ?? '');
    if (empty($values['email'])) {
        $errors['email'] = 'Email обязателен для заполнения';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный email адрес';
    } elseif (strlen($values['email']) > 100) {
        $errors['email'] = 'Email не должен превышать 100 символов';
    }

    // 4. Валидация даты рождения
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
            $errors['birthdate'] = 'Некорректный формат даты';
        }
    }

    // 5. Валидация пола
    $values['gender'] = $post_data['gender'] ?? '';
    if (empty($values['gender'])) {
        $errors['gender'] = 'Укажите пол';
    } elseif (!in_array($values['gender'], ['male', 'female'])) {
        $errors['gender'] = 'Выберите пол из предложенных вариантов';
    }

    // 6. Валидация языков программирования
    $values['languages'] = $post_data['languages'] ?? [];
    if (empty($values['languages'])) {
        $errors['languages'] = 'Выберите хотя бы один язык';
    }

    // 7. Валидация биографии
    $values['bio'] = trim($post_data['bio'] ?? '');
    if (!empty($values['bio']) && strlen($values['bio']) > 1000) {
        $errors['bio'] = 'Биография не должна превышать 1000 символов';
    }

    // 8. Валидация соглашения
    $values['agreement'] = isset($post_data['agreement']) ? 1 : 0;
    if (!$values['agreement']) {
        $errors['agreement'] = 'Необходимо принять соглашение';
    }

    return ['values' => $values, 'errors' => $errors];
}

function update_user_data($db, $user_id, $values)
{
    try {
        // 1. Обновляем основную информацию в таблице applications
        $stmt = $db->prepare("
            UPDATE applications 
            SET 
                fio = :fio,
                phone = :phone,
                email = :email,
                birthdate = :birthdate,
                gender = :gender,
                bio = :bio,
                agreement = :agreement,
                updated_at = NOW()
            WHERE user_id = :user_id
        ");

        $stmt->execute([
            ':fio' => $values['fio'],
            ':phone' => $values['phone'],
            ':email' => $values['email'],
            ':birthdate' => $values['birthdate'],
            ':gender' => $values['gender'],
            ':bio' => $values['bio'],
            ':agreement' => $values['agreement'],
            ':user_id' => $user_id
        ]);

        // 2. Получаем ID связанной заявки
        $stmt = $db->prepare("SELECT id FROM applications WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $app_id = $stmt->fetchColumn();

        if (!$app_id) {
            throw new Exception("Application not found for user ID: $user_id");
        }

        // 3. Обновляем языки программирования
        // Сначала удаляем все существующие связи
        $stmt = $db->prepare("DELETE FROM application_languages WHERE application_id = :app_id");
        $stmt->execute([':app_id' => $app_id]);

        // Затем добавляем новые
        $stmt = $db->prepare("
            INSERT INTO application_languages (application_id, language_id) 
            VALUES (:app_id, :lang_id)
        ");

        foreach ($values['languages'] as $lang_id) {
            $lang_id = (int) $lang_id;
            if ($lang_id <= 0)
                continue; // Пропускаем некорректные ID

            $stmt->execute([
                ':app_id' => $app_id,
                ':lang_id' => $lang_id
            ]);
        }

        // Логируем успешное обновление
        error_log("User data updated successfully for user ID: $user_id");

    } catch (PDOException $e) {
        error_log("Database error in update_user_data: " . $e->getMessage());
        throw new Exception("Ошибка при обновлении данных пользователя");
    } catch (Exception $e) {
        error_log("Error in update_user_data: " . $e->getMessage());
        throw $e;
    }
}

function create_new_user($db, $values) {
    try {
        // 1. Создаем запись в таблице user
        $stmt = $db->prepare("
            INSERT INTO user (fio, tel, email, bdate, gender, bio, ccheck) 
            VALUES (:fio, :phone, :email, :birthdate, :gender, :bio, :agreement)
        ");
        
        $stmt->execute([
            ':fio' => $values['fio'],
            ':phone' => $values['phone'],
            ':email' => $values['email'],
            ':birthdate' => $values['birthdate'],
            ':gender' => $values['gender'],
            ':bio' => $values['bio'],
            ':agreement' => $values['agreement']
        ]);
        
        $user_id = $db->lastInsertId();
        
        // 2. Создаем учетные данные в user_login
        $login = 'user_' . bin2hex(random_bytes(4));
        $password = substr(bin2hex(random_bytes(8)), 0, 8);
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $db->prepare("
            INSERT INTO user_login (user_id, login, password, role)
            VALUES (:user_id, :login, :password, 'user')
        ");
        $stmt->execute([
            ':user_id' => $user_id,
            ':login' => $login,
            ':password' => $password_hash
        ]);
        
        // 3. Добавляем выбранные языки программирования
        $stmt = $db->prepare("
            INSERT INTO user_language (user_id, lang_id)
            VALUES (:user_id, :lang_id)
        ");
        
        foreach ($values['languages'] as $lang_id) {
            $lang_id = (int) $lang_id;
            if ($lang_id <= 0) continue;
            
            $stmt->execute([
                ':user_id' => $user_id,
                ':lang_id' => $lang_id
            ]);
        }
        
        return [
            'login' => $login,
            'password' => $password,
            'user_id' => $user_id
        ];
    } catch (PDOException $e) {
        error_log("Database error in create_new_user: " . $e->getMessage());
        throw new Exception("Ошибка при создании нового пользователя");
    }
}

function handle_validation_errors($validation, $request)
{
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

    if ($is_ajax) {
        return json_response([
            'success' => false,
            'errors' => $validation['errors']
        ]);
    }

    return redirect_with_errors($validation['errors'], $validation['values']);
}

function process_form($db, $values, $is_auth)
{
    try {
        $db->beginTransaction();

        if ($is_auth) {
            // Обновление существующего пользователя
            update_user_data($db, $_SESSION['uid'], $values);
            $db->commit();
            return redirect_with_message('Данные успешно обновлены');
        } else {
            // Создание нового пользователя
            $credentials = create_new_user($db, $values);
            $db->commit();

            $_SESSION['new_login'] = $credentials['login'];
            $_SESSION['new_password'] = $credentials['password'];
            header('Location: /success');
            exit;
        }
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Form Processing Error: " . $e->getMessage());
        return redirect_with_errors(['general' => 'Ошибка обработки данных']);
    }
}

function json_response($data)
{
    header('Content-Type: application/json');
    die(json_encode($data));
}

function redirect_with_errors($errors, $values = [])
{
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_values'] = $values;
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

function redirect_with_message($message)
{
    $_SESSION['form_messages'] = [$message];
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}