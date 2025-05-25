<?php
function admin_panel_get($request) {
    global $db;

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Аутентификация
    if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
        return authenticate();
    }

    $admin_login = $_SERVER['PHP_AUTH_USER'];
    $admin_password = $_SERVER['PHP_AUTH_PW'];

    try {
        $stmt = $db->prepare("SELECT id, password FROM admin WHERE login = ?");
        $stmt->execute([$admin_login]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            return authenticate();
        }

        if (!password_verify($admin_password, $admin['password'])) {
            return authenticate();
        }

        // Генерация CSRF токена
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $csrf_token = $_SESSION['csrf_token'];

        // Получение данных для отображения
        $users = get_all_users($db);
        $language_stats = get_language_statistics($db);

        // Формирование массива данных для шаблона
        $template_data = [
            'users' => $users,
            'language_stats' => $language_stats,
            'csrf_token' => $csrf_token,
            'admin_message' => isset($_SESSION['admin_message']) ? $_SESSION['admin_message'] : '',
        ];

        unset($_SESSION['admin_message']);

        return theme('admin_panel', ['#content' => $template_data]);

    } catch (PDOException $e) {
        error_log("Ошибка базы данных: " . $e->getMessage());
        die("Ошибка: Произошла ошибка на сервере.");
    }
}

function admin_panel_post($request, $url_param_1 = null) {
    global $db;
    session_start();

    // Проверка CSRF токена
    if (empty($_SESSION['csrf_token']) || !isset($request['post']['csrf_token']) || $request['post']['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed.');
    }

    // Обработка удаления
    if (isset($request['post']['action']) && $request['post']['action'] == 'delete' && isset($request['post']['id']) && is_numeric($request['post']['id'])) {
        $id_to_delete = intval($request['post']['id']);
        try {
            delete_user($db, $id_to_delete);
            $_SESSION['admin_message'] = '<p style="color: green;">Пользователь успешно удален.</p>';
        } catch (PDOException $e) {
            error_log("Ошибка при удалении пользователя: " . $e->getMessage());
            $_SESSION['admin_message'] = '<p style="color: red;">Ошибка при удалении пользователя.</p>';
        }
        return redirect('admin');
    }

    // Обработка редактирования
    if (isset($request['post']['action']) && $request['post']['action'] == 'edit' && isset($request['post']['id']) && is_numeric($request['post']['id'])) {
        $id_to_edit = intval($request['post']['id']);

        $validation_errors = [];

        $fio = isset($request['post']['fio']) ? trim(htmlspecialchars($request['post']['fio'])) : '';
        if (!preg_match("/^[a-zA-Zа-яА-Я\s-]+$/u", $fio)) {
            $validation_errors['fio'] = 'ФИО должно содержать только буквы, пробелы и дефисы.';
        }

        $tel = isset($request['post']['tel']) ? trim(htmlspecialchars($request['post']['tel'])) : '';
        if (!preg_match("/^[0-9\-\(\)\+]+$/", $tel)) {
            $validation_errors['tel'] = 'Телефон должен содержать только цифры, скобки, дефисы и знаки "+".';
        }

        $email = isset($request['post']['email']) ? trim(htmlspecialchars($request['post']['email'])) : '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validation_errors['email'] = 'Некорректный email.';
        }

        $bdate = isset($request['post']['bdate']) ? trim(htmlspecialchars($request['post']['bdate'])) : '';
        try {
            if (!empty($bdate)) {
                $bdate_obj = new DateTime($bdate);
                $bdate = $bdate_obj->format('Y-m-d');
            }
        } catch (Exception $e) {
            $validation_errors['bdate'] = 'Некорректная дата рождения.';
            $bdate = '';
        }

        $gender = isset($request['post']['gender']) ? trim(htmlspecialchars($request['post']['gender'])) : '';
        if (empty($gender)) {
            $validation_errors['gender'] = 'Укажите пол.';
        }

        $bio = isset($request['post']['bio']) ? trim(htmlspecialchars($request['post']['bio'])) : '';
        if (strlen($bio) > 1000) {
            $validation_errors['bio'] = 'Биография слишком длинная (максимум 1000 символов).';
        }

        $ccheck = isset($request['post']['ccheck']) ? intval($request['post']['ccheck']) : 0;

        $languages = isset($request['post']['languages']) && is_array($request['post']['languages']) ? array_map('intval', $request['post']['languages']) : [];

        if (empty($validation_errors)) {
            try {
                update_user($db, $id_to_edit, $fio, $tel, $email, $bdate, $gender, $bio, $ccheck, $languages);
                $_SESSION['admin_message'] = '<p style="color: green;">Данные пользователя успешно обновлены.</p>';
            } catch (PDOException $e) {
                error_log("Ошибка при обновлении данных пользователя: " . $e->getMessage());
                $_SESSION['admin_message'] = '<p style="color: red;">Ошибка при обновлении данных пользователя.</p>';
            }
        } else {
            $_SESSION['admin_message'] = '<p style="color: red;">Обнаружены ошибки валидации:</p><ul>';
            foreach ($validation_errors as $field => $error) {
                $_SESSION['admin_message'] .= '<li>' . htmlspecialchars($error) . '</li>';
            }
            $_SESSION['admin_message'] .= '</ul>';
        }
        return redirect('admin');
    }
    
    return redirect('admin');
}

function authenticate() {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Area"');
    return theme('401');
}

// Вспомогательные функции для работы с БД
function get_all_users($db) {
    $stmt = $db->prepare("
        SELECT u.*, ul.login, ul.role 
        FROM user u
        JOIN user_login ul ON u.id = ul.user_id
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_language_statistics($db) {
    $stmt = $db->prepare("
        SELECT l.name, COUNT(ul.lang_id) AS count
        FROM language l
        LEFT JOIN user_language ul ON l.id = ul.lang_id
        GROUP BY l.id
        ORDER BY count DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function delete_user($db, $id) {
    // Каскадное удаление сработает благодаря ON DELETE CASCADE
    $stmt = $db->prepare("DELETE FROM user WHERE id = ?");
    $stmt->execute([$id]);
}

function update_user($db, $id, $fio, $tel, $email, $bdate, $gender, $bio, $ccheck, $languages) {
    $stmt = $db->prepare("
        UPDATE user SET 
            fio = ?, 
            tel = ?, 
            email = ?, 
            bdate = ?, 
            gender = ?, 
            bio = ?, 
            ccheck = ?
        WHERE id = ?
    ");
    $stmt->execute([$fio, $tel, $email, $bdate, $gender, $bio, $ccheck, $id]);

    // Обновляем языки
    $stmt = $db->prepare("DELETE FROM user_language WHERE user_id = ?");
    $stmt->execute([$id]);

    $stmt = $db->prepare("INSERT INTO user_language (user_id, lang_id) VALUES (?, ?)");
    foreach ($languages as $lang_id) {
        $stmt->execute([$id, $lang_id]);
    }
}
?>