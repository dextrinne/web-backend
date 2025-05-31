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
    $required = ['fio', 'tel', 'email', 'bdate', 'gender', 'languages', 'ccheck'];
    foreach ($required as $field) {
        if (empty($request['post'][$field])) {
            return json_response([
                'success' => false,
                'errors' => [$field => 'Это поле обязательно']
            ]);
        }
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
?>