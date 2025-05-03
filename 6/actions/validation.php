<?php
function validateFormData($db, &$errors, &$values, $abilities) {
    $errors = false;
    
    if (empty($_POST['fio'])) {
        setcookie('fio_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        if (strlen($_POST['fio']) > 150) {
            setcookie('fio_error', '2', time() + 24 * 60 * 60);
            $errors = TRUE;
        } elseif (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s]+$/u", $_POST['fio'])) {
            setcookie('fio_error', '3', time() + 24 * 60 * 60);
            $errors = TRUE;
        }
    }

    if (empty($_POST['tel']) || !preg_match('/^\+7\d{10}$/', $_POST['tel'])) {
        setcookie('tel_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }

    if (empty($_POST["email"]) || !filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        setcookie('email_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }

    $fav_languages = $_POST["abilities"] ?? [];
    if (empty($fav_languages)) {
        setcookie('abilities_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        foreach ($fav_languages as $ability) {
            if (empty($abilities[$ability])) {
                setcookie('abilities_error', '1', time() + 24 * 60 * 60);
                $errors = TRUE;
            }
        }
    }

    if (empty($_POST['bdate']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['bdate'])) {
        setcookie('bdate_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }

    if (empty($_POST['radio'])) {
        setcookie('radio_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }

    if (empty($_POST['bio'])) {
        setcookie('bio_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } elseif(strlen($_POST['bio']) > 512) {
        setcookie('bio_error', '2', time() + 24 * 60 * 60);
        $errors = TRUE;
    } elseif(preg_match('/[<>{}\[\]]|<script|<\?php/i', $_POST['bio'])) {
        setcookie('bio_error', '3', time() + 24 * 60 * 60);
        $errors = TRUE;
    }

    if (empty($_POST['ccheck'])) {
        setcookie('ccheck_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }

    return !$errors;
}

function handleValidationErrors(&$messages) {
    $errorsMap = [
        'fio' => [
            '1' => 'Введите ФИО.',
            '2' => 'ФИО не должно превышать 150 символов.',
            '3' => 'ФИО должно содержать только буквы и пробелы.'
        ],
        'tel' => ['1' => 'Введите корректный номер телефона.'],
        'email' => ['1' => 'Введите корректный email.'],
        'abilities' => ['1' => 'Выберите любимый язык программирования.'],
        'bdate' => ['1' => 'Введите корректную дату рождения.'],
        'radio' => ['1' => 'Выберите пол.'],
        'bio' => [
            '1' => 'Заполните биографию.',
            '2' => 'Количество символов в поле "биография" не должно превышать 512.',
            '3' => 'Поле "биография" содержит недопустимые символы.'
        ],
        'ccheck' => ['1' => 'Подтвердите ознакомление с контрактом.']
    ];

    foreach ($errorsMap as $field => $errorTypes) {
        if (!empty($_COOKIE["{$field}_error"])) {
            $errorCode = $_COOKIE["{$field}_error"];
            if (isset($errorTypes[$errorCode])) {
                $messages[] = '<div class="error">' . $errorTypes[$errorCode] . '</div>';
            }
            setcookie("{$field}_error", '', time() - 3600);
        }
    }
}

function fillUserValues($db, $login = null) {
    $values = [];
    
    if ($login) {
        if ($userData = getUserData($db, $login)) {
            foreach (['fio', 'tel', 'email', 'bdate', 'bio', 'ccheck'] as $field) {
                $values[$field] = htmlspecialchars($userData[$field] ?? '');
            }
            $values['radio'] = htmlspecialchars($userData['gender'] ?? '');
            $values['abilities'] = !empty($userData['abilities']) ? explode(',', $userData['abilities']) : [];
        }
    } else {
        foreach (['fio', 'tel', 'email', 'bdate', 'radio', 'bio', 'ccheck'] as $field) {
            $values[$field] = $_COOKIE["{$field}_value"] ?? '';
        }
        $values['abilities'] = !empty($_COOKIE['abilities_value']) ? explode(',', $_COOKIE['abilities_value']) : [];
    }
    
    return $values;
}

function updateUserData($db, $user_id, $post_data, $abilities = []) {
    try {
        $db->beginTransaction();
        
        // Обновляем основные данные пользователя
        $stmt = $db->prepare("
            UPDATE user SET 
                fio = ?, tel = ?, email = ?, bdate = ?, 
                gender = ?, bio = ?, ccheck = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $post_data['fio'], $post_data['tel'], $post_data['email'], $post_data['bdate'],
            $post_data['gender'], $post_data['bio'], isset($post_data['ccheck']) ? 1 : 0,
            $user_id
        ]);
        
        // Обновляем языки программирования
        $stmt = $db->prepare("DELETE FROM user_language WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        if (!empty($abilities)) {
            $stmt = $db->prepare("INSERT INTO user_language (user_id, lang_id) VALUES (?, ?)");
            foreach ($abilities as $lang_id) {
                $stmt->execute([$user_id, $lang_id]);
            }
        }
        
        $db->commit();
        return true;
    } catch (PDOException $e) {
        $db->rollBack();
        return false;
    }
}
?>
