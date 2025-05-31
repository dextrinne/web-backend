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
    // Инициализация сессии
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();

    // Подключение к БД с улучшенной обработкой ошибок
    try {
        $db = new PDO(
            'mysql:host=localhost;dbname=u68595;charset=utf8mb4',
            'u68595',
            '6788124',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false
            ]
        );
    } catch (PDOException $e) {
        error_log("DB Connection Failed: ".$e->getMessage());
        return json_response([
            'success' => false,
            'errors' => ['general' => 'Database connection failed']
        ]);
    }

    // Валидация CSRF токена
    if (empty($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== ($request['post']['csrf_token'] ?? '')) {
        return json_response([
            'success' => false,
            'errors' => ['general' => 'Invalid CSRF token']
        ]);
    }

    // Базовая валидация данных
    /*$required = ['fio', 'tel', 'email', 'bdate', 'gender', 'languages', 'ccheck'];
    foreach ($required as $field) {
        if (empty($request['post'][$field])) {
            return json_response([
                'success' => false,
                'errors' => [$field => 'Это поле обязательно']
            ]);
        }
    }*/
    $validation = validate_form_data($request['post']);
    if (!empty($validation['errors'])) {
        return json_response([
            'success' => false,
            'errors' => $validation['errors']
        ]);
    }

    // Обработка данных
    try {
        $db->beginTransaction();

        // 1. Вставка в таблицу user
        $stmt = $db->prepare("INSERT INTO user (fio, tel, email, bdate, gender, bio, ccheck) 
                             VALUES (:fio, :tel, :email, :bdate, :gender, :bio, :ccheck)");
        $stmt->execute([
            ':fio' => $request['post']['fio'],
            ':tel' => $request['post']['tel'],
            ':email' => $request['post']['email'],
            ':bdate' => $request['post']['bdate'],
            ':gender' => $request['post']['gender'],
            ':bio' => $request['post']['bio'] ?? '',
            ':ccheck' => !empty($request['post']['ccheck']) ? 1 : 0
        ]);
        $user_id = $db->lastInsertId();

        // 2. Создание учётных данных
        $login = 'user_'.bin2hex(random_bytes(4));
        $password = bin2hex(random_bytes(4));
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $db->prepare("INSERT INTO user_login (user_id, login, password, role) 
                             VALUES (:user_id, :login, :password, 'user')");
        $stmt->execute([
            ':user_id' => $user_id,
            ':login' => $login,
            ':password' => $password_hash
        ]);

        // 3. Добавление языков
        if (!empty($request['post']['languages'])) {
            $stmt = $db->prepare("INSERT INTO user_language (user_id, lang_id) VALUES (:user_id, :lang_id)");
            foreach ((array)$request['post']['languages'] as $lang_id) {
                $stmt->execute([':user_id' => $user_id, ':lang_id' => (int)$lang_id]);
            }
        }

        $db->commit();

        return json_response([
            'success' => true,
            'login' => $login,
            'password' => $password,
            'message' => 'Регистрация успешно завершена'
        ]);

    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Database Error: ".$e->getMessage());
        return json_response([
            'success' => false,
            'errors' => ['general' => 'Database error: '.$e->getMessage()]
        ]);
    }
}

function json_response($data) {
    header('Content-Type: application/json');
    die(json_encode($data));
}

function validate_form_data($post_data) {
    $values = [];
    $errors = [];

    // 1. Валидация ФИО
    $values['fio'] = trim($post_data['fio'] ?? '');
    if (empty($values['fio'])) {
        $errors['fio'] = 'ФИО обязательно для заполнения';
    } else {
        // Проверка длины
        if (strlen($values['fio']) > 150) {
            $errors['fio'] = 'ФИО не должно превышать 150 символов';
        }
        // Проверка на допустимые символы (буквы, пробелы и дефисы)
        if (!preg_match('/^[\p{Cyrillic}\p{Latin}\s\-]+$/u', $values['fio'])) {
            $errors['fio'] = 'ФИО должно содержать только буквы и дефисы';
        }
    }

    // 2. Валидация телефона
    $values['tel'] = preg_replace('/\s+/', '', trim($post_data['tel'] ?? ''));
    if (empty($values['tel'])) {
        $errors['tel'] = 'Телефон обязателен для заполнения';
    } elseif (!preg_match('/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $values['tel'])) {
        $errors['tel'] = 'Введите телефон в формате +7(XXX)XXX-XX-XX';
    }

    // 3. Валидация email
    $values['email'] = strtolower(trim($post_data['email'] ?? ''));
    if (empty($values['email'])) {
        $errors['email'] = 'Email обязателен для заполнения';
    } else {
        // Проверка длины
        if (strlen($values['email']) > 255) {
            $errors['email'] = 'Email не должен превышать 255 символов';
        }
        // Проверка формата email
        if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Введите корректный email адрес';
        }
    }

    // 4. Валидация даты рождения
    $values['bdate'] = $post_data['bdate'] ?? '';
    if (empty($values['bdate'])) {
        $errors['bdate'] = 'Дата рождения обязательна';
    } else {
        try {
            $birthdate = new DateTime($values['bdate']);
            $now = new DateTime();
            $minDate = new DateTime('1900-01-01');
            
            if ($birthdate > $now) {
                $errors['bdate'] = 'Дата рождения не может быть в будущем';
            } elseif ($birthdate < $minDate) {
                $errors['bdate'] = 'Дата рождения слишком ранняя';
            } else {
                $age = $now->diff($birthdate)->y;
                if ($age < 18) {
                    $errors['bdate'] = 'Возраст должен быть 18+ лет';
                }
            }
        } catch (Exception $e) {
            $errors['bdate'] = 'Некорректный формат даты';
        }
    }

    // 5. Валидация пола
    $values['gender'] = $post_data['gender'] ?? '';
    if (empty($values['gender'])) {
        $errors['gender'] = 'Укажите пол';
    } elseif (!in_array($values['gender'], ['Male', 'Female'])) {
        $errors['gender'] = 'Выберите пол из предложенных вариантов';
    }

    // 6. Валидация языков программирования
    $values['languages'] = [];
    if (!empty($post_data['languages'])) {
        foreach ((array)$post_data['languages'] as $lang_id) {
            if (ctype_digit((string)$lang_id)) {
                $values['languages'][] = (int)$lang_id;
            }
        }
    }
    if (empty($values['languages'])) {
        $errors['languages'] = 'Выберите хотя бы один язык';
    }

    // 7. Валидация биографии
    $values['bio'] = strip_tags(trim($post_data['bio'] ?? ''));
    if (strlen($values['bio']) > 5000) {
        $errors['bio'] = 'Биография не должна превышать 5000 символов';
    }

    // 8. Валидация соглашения
    $values['ccheck'] = isset($post_data['ccheck']) ? 1 : 0;
    if (!$values['ccheck']) {
        $errors['ccheck'] = 'Необходимо принять соглашение';
    }

    return ['values' => $values, 'errors' => $errors];
}
?>