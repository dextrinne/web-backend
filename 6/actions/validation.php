<?php
function validateFormData($db, &$errors, &$values, $abilities) {
    $errors = FALSE;

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

    setcookie('fio_value', $_POST['fio'], time() + 30 * 24 * 60 * 60);
    setcookie('tel_value', $_POST['tel'], time() + 30 * 24 * 60 * 60);
    setcookie('email_value', $_POST['email'], time() + 30 * 24 * 60 * 60);
    setcookie('abilities_value', !empty($fav_languages) ? implode(',', $fav_languages) : '', time() + 30 * 24 * 60 * 60);
    setcookie('bdate_value', $_POST['bdate'], time() + 30 * 24 * 60 * 60);
    setcookie('radio_value', $_POST['radio'], time() + 30 * 24 * 60 * 60);
    setcookie('bio_value', $_POST['bio'], time() + 30 * 24 * 60 * 60);
    setcookie('ccheck_value', $_POST['ccheck'], time() + 30 * 24 * 60 * 60);

    return !$errors;
}

function generateRandomString($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function handleValidationErrors(&$messages) {
    $errors = array();
    $errors['fio'] = !empty($_COOKIE['fio_error']);
    $errors['tel'] = !empty($_COOKIE['tel_error']);
    $errors['email'] = !empty($_COOKIE['email_error']);
    $errors['abilities'] = !empty($_COOKIE['abilities_error']);
    $errors['bdate'] = !empty($_COOKIE['bdate_error']);
    $errors['radio'] = !empty($_COOKIE['radio_error']);
    $errors['bio'] = !empty($_COOKIE['bio_error']);
    $errors['ccheck'] = !empty($_COOKIE['ccheck_error']);

    if ($errors['fio']) {
        if ($_COOKIE['fio_error'] == '1') {
            $messages[] = '<div class="error">Введите ФИО.</div>';
        } elseif ($_COOKIE['fio_error'] == '2') {
            $messages[] = '<div class="error">ФИО не должно превышать 150 символов.</div>';
        } else {
            $messages[] = '<div class="error">ФИО должно содержать только буквы и пробелы.</div>';
        }
        setcookie('fio_error', '', 100000);
    }

    if ($errors['tel']) {
        setcookie('tel_error', '', 100000);
        $messages[] = '<div class="error">Введите корректный номер телефона.</div>';
    }
    if ($errors['email']) {
        setcookie('email_error', '', 100000);
        $messages[] = '<div class="error">Введите корректный email.</div>';
    }
    if ($errors['abilities']) {
        setcookie('abilities_error', '', 100000);
        $messages[] = '<div class="error">Выберите любимый язык программирования.</div>';
    }
    if ($errors['bdate']) {
        setcookie('bdate_error', '', 100000);
        $messages[] = '<div class="error">Введите корректную дату рождения.</div>';
    }
    if ($errors['radio']) {
        setcookie('radio_error', '', 100000);
        $messages[] = '<div class="error">Выберите пол.</div>';
    }
    if ($errors['bio']) {
        if($_COOKIE['bio_error']=='1'){
          $messages[] = '<div class="error">Заполните биографию.</div>';
        }
        elseif($_COOKIE['bio_error']=='2'){
          $messages[] = '<div class="error">Количество символов в поле "биография" не должно превышать 512.</div>';
        }
        elseif($_COOKIE['bio_error']=='3'){
          $messages[] = '<div class="error">Поле "биография" содержит недопустимые символы.</div>';
        }
        setcookie('bio_error', '', 100000);
    }
    if ($errors['ccheck']) {
        setcookie('ccheck_error', '', 100000);
        $messages[] = '<div class="error">Подтвердите ознакомление с контрактом.</div>';
    }
}

function fillUserValues($db, $login = null) {
    $values = array();
    if ($login) {
        $user_data = getUserData($db, $login);
        if ($user_data) {
            $values['fio'] = htmlspecialchars($user_data['fio']);
            $values['tel'] = htmlspecialchars($user_data['tel']);
            $values['email'] = htmlspecialchars($user_data['email']);
            $values['bdate'] = htmlspecialchars($user_data['bdate']);
            $values['radio'] = htmlspecialchars($user_data['gender']);
            $values['bio'] = htmlspecialchars($user_data['bio']);
            $values['ccheck'] = htmlspecialchars($user_data['ccheck']);
            $values['abilities'] = !empty($user_data['abilities']) ? explode(',', $user_data['abilities']) : array();
        }
    } else {
        $values['fio'] = empty($_COOKIE['fio_value']) ? '' : $_COOKIE['fio_value'];
        $values['tel'] = empty($_COOKIE['tel_value']) ? '' : $_COOKIE['tel_value'];
        $values['email'] = empty($_COOKIE['email_value']) ? '' : $_COOKIE['email_value'];
        $values['abilities'] = empty($_COOKIE['abilities_value']) ? '' : $_COOKIE['abilities_value'];
        $values['bdate'] = empty($_COOKIE['bdate_value']) ? '' : $_COOKIE['bdate_value'];
        $values['radio'] = empty($_COOKIE['radio_value']) ? '' : $_COOKIE['radio_value'];
        $values['bio'] = empty($_COOKIE['bio_value']) ? '' : $_COOKIE['bio_value'];
        $values['ccheck'] = empty($_COOKIE['ccheck_value']) ? '' : $_COOKIE['ccheck_value'];
    }
    return $values;
}

function updateUserData($db, $user_id, $post_data, $abilities = array()) {
    try {
        $stmt = $db->prepare("
            UPDATE user 
            SET fio = ?, tel = ?, email = ?, bdate = ?, gender = ?, bio = ?, ccheck = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $post_data['fio'],
            $post_data['tel'],
            $post_data['email'],
            $post_data['bdate'],
            $post_data['gender'],
            $post_data['bio'],
            isset($post_data["ccheck"]) ? 1 : 0,
            $user_id
        ]);

        $stmt = $db->prepare("DELETE FROM user_language WHERE user_id = ?");
        $stmt->execute([$user_id]);

        if (!empty($abilities) && is_array($abilities)) {
            $stmt = $db->prepare("INSERT INTO user_language (user_id, lang_id) VALUES (?, ?)");
            foreach ($abilities as $ability) {
                $stmt->execute([$user_id, $ability]);
            }
        }
        return true;
    } catch (PDOException $e) {
        return false;
    }
}
?>
