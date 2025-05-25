<?php
function admin_panel_post($request, $url_param_1 = null) {
    global $db;
    session_start();

    // Проверка CSRF токена
    if (empty($_SESSION['csrf_token']) || !isset($request['post']['csrf_token']) || 
        $request['post']['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed.');
    }

    // Обработка удаления
    if (isset($request['post']['action']) && $request['post']['action'] == 'delete' && 
        isset($request['post']['id']) && is_numeric($request['post']['id'])) {
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

    return redirect('admin');
}

function authenticate() {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Area"');
    return theme('401');
}

function get_all_users($db) {
    $stmt = $db->prepare("
        SELECT u.*, GROUP_CONCAT(l.name SEPARATOR ', ') AS languages
        FROM user u
        LEFT JOIN user_language ul ON u.id = ul.user_id
        LEFT JOIN language l ON ul.lang_id = l.id
        GROUP BY u.id
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_language_statistics($db) {
    $stmt = $db->prepare("
        SELECT l.name, COUNT(ul.user_id) AS user_count
        FROM language l
        LEFT JOIN user_language ul ON l.id = ul.lang_id
        GROUP BY l.id
        ORDER BY user_count DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function delete_user($db, $id) {
    $db->beginTransaction();
    try {
        $stmt = $db->prepare("DELETE FROM user_language WHERE user_id = ?");
        $stmt->execute([$id]);
        
        $stmt = $db->prepare("DELETE FROM user_login WHERE user_id = ?");
        $stmt->execute([$id]);
        
        $stmt = $db->prepare("DELETE FROM user WHERE id = ?");
        $stmt->execute([$id]);
        
        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        throw $e;
    }
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