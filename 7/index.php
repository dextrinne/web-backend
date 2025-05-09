<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'includes/security_headers.php';
echo "<link rel='stylesheet' href='style.css'>";
include('./actions/db.php');
include('./actions/functions.php');
include('./actions/validation.php');

session_start();

$abilities = getAbilities($db);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = [];
    $values = [];

    if (isset($_SESSION['login'])) {
        $messages[] = ' Вы вошли как: ' . htmlspecialchars($_SESSION['login']);
        $logoutButton = '<a href="login.php?exit=1">Выйти</a>';

        $user_data = getUserData($db, $_SESSION['login']);

        if ($user_data) {
            $values['fio'] = htmlspecialchars($user_data['fio']);
            $values['tel'] = htmlspecialchars($user_data['tel']);
            $values['email'] = htmlspecialchars($user_data['email']);
            $values['bdate'] = htmlspecialchars($user_data['bdate']);
            $values['radio'] = htmlspecialchars($user_data['gender']);
            $values['bio'] = htmlspecialchars($user_data['bio']);
            $values['ccheck'] = htmlspecialchars($user_data['ccheck']);
            $values['abilities'] = !empty($user_data['abilities']) ? explode(',', $user_data['abilities']) : [];
        }
    } else {
        $logoutButton = '';
        // Загружаем значения из cookies только для неавторизованных пользователей
        $values['fio'] = empty($_COOKIE['fio_value']) ? '' : $_COOKIE['fio_value'];
        $values['tel'] = empty($_COOKIE['tel_value']) ? '' : $_COOKIE['tel_value'];
        $values['email'] = empty($_COOKIE['email_value']) ? '' : $_COOKIE['email_value'];
        $values['abilities'] = empty($_COOKIE['abilities_value']) ? [] : explode(',', $_COOKIE['abilities_value']);
        $values['bdate'] = empty($_COOKIE['bdate_value']) ? '' : $_COOKIE['bdate_value'];
        $values['radio'] = empty($_COOKIE['radio_value']) ? '' : $_COOKIE['radio_value'];
        $values['bio'] = empty($_COOKIE['bio_value']) ? '' : $_COOKIE['bio_value'];
        $values['ccheck'] = empty($_COOKIE['ccheck_value']) ? '' : $_COOKIE['ccheck_value'];
    }

    // Сообщение об успешном сохранении.
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        setcookie('login', '', 100000);
        setcookie('pass', '', 100000);

        $messages[] = ' Спасибо, результаты сохранены.';

        if (!empty($_COOKIE['login']) && !empty($_COOKIE['pass'])) {
            $messages[] = sprintf(
                'Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong> и паролем <strong>%s</strong>.',
                strip_tags($_COOKIE['login']),
                strip_tags($_COOKIE['pass'])
            );
        }
    }

    // Выдаем сообщения об ошибках.
    $errors = [
        'fio' => !empty($_COOKIE['fio_error']),
        'tel' => !empty($_COOKIE['tel_error']),
        'email' => !empty($_COOKIE['email_error']),
        'abilities' => !empty($_COOKIE['abilities_error']),
        'bdate' => !empty($_COOKIE['bdate_error']),
        'radio' => !empty($_COOKIE['radio_error']),
        'bio' => !empty($_COOKIE['bio_error']),
        'ccheck' => !empty($_COOKIE['ccheck_error'])
    ];

    if ($errors['fio']) {
        $messages[] = getErrorMessage('fio', $_COOKIE['fio_error']);
        setcookie('fio_error', '', 100000);
    }

    if ($errors['tel']) {
        $messages[] = getErrorMessage('tel', $_COOKIE['tel_error']);
        setcookie('tel_error', '', 100000);
    }

    if ($errors['email']) {
        $messages[] = getErrorMessage('email', $_COOKIE['email_error']);
        setcookie('email_error', '', 100000);
    }

    if ($errors['abilities']) {
        $messages[] = getErrorMessage('abilities', $_COOKIE['abilities_error']);
        setcookie('abilities_error', '', 100000);
    }

    if ($errors['bdate']) {
        $messages[] = getErrorMessage('bdate', $_COOKIE['bdate_error']);
        setcookie('bdate_error', '', 100000);
    }

    if ($errors['radio']) {
        $messages[] = getErrorMessage('radio', $_COOKIE['radio_error']);
        setcookie('radio_error', '', 100000);
    }

    if ($errors['bio']) {
        $messages[] = getErrorMessage('bio', $_COOKIE['bio_error']);
        setcookie('bio_error', '', 100000);
    }

    if ($errors['ccheck']) {
        $messages[] = getErrorMessage('ccheck', $_COOKIE['ccheck_error']);
        setcookie('ccheck_error', '', 100000);
    }

    echo $logoutButton;
    include('form.php');
} else {
    $errors = [];
    $values = [];

    $is_valid = validateFormData($db, $errors, $values, $abilities);

    if (!$is_valid) {
        header('Location: index.php');
        exit();
    } else {
        // Очистка кук с ошибками
        $cookieParams = session_get_cookie_params();
        $domain = !empty($cookieParams['domain']) ? $cookieParams['domain'] : '';
        $secure = !empty($cookieParams['secure']) ? $cookieParams['secure'] : false;
        $httponly = !empty($cookieParams['httponly']) ? $cookieParams['httponly'] : false;

        $clearCookies = [
            'fio_error',
            'tel_error',
            'email_error',
            'abilities_error',
            'bdate_error',
            'radio_error',
            'bio_error',
            'ccheck_error'
        ];
        foreach ($clearCookies as $cookie) {
            setcookie($cookie, '', time() - 3600, $cookieParams['path'], $domain, $secure, $httponly);
        }

        // Сохраняем значения только для неавторизованных пользователей
        if (!isset($_SESSION['login'])) {
            setcookie('fio_value', $_POST['fio'], time() + 30 * 24 * 60 * 60, $cookieParams['path'], $domain, $secure, $httponly);
            setcookie('tel_value', $_POST['tel'], time() + 30 * 24 * 60 * 60, $cookieParams['path'], $domain, $secure, $httponly);
            setcookie('email_value', $_POST['email'], time() + 30 * 24 * 60 * 60, $cookieParams['path'], $domain, $secure, $httponly);
            setcookie('abilities_value', !empty($_POST["abilities"]) ? implode(',', $_POST["abilities"]) : '', time() + 30 * 24 * 60 * 60, $cookieParams['path'], $domain, $secure, $httponly);
            setcookie('bdate_value', $_POST['bdate'], time() + 30 * 24 * 60 * 60, $cookieParams['path'], $domain, $secure, $httponly);
            setcookie('radio_value', $_POST['radio'], time() + 30 * 24 * 60 * 60, $cookieParams['path'], $domain, $secure, $httponly);
            setcookie('bio_value', $_POST['bio'], time() + 30 * 24 * 60 * 60, $cookieParams['path'], $domain, $secure, $httponly);
            setcookie('ccheck_value', $_POST['ccheck'], time() + 30 * 24 * 60 * 60, $cookieParams['path'], $domain, $secure, $httponly);
        }
    }

    $login = generateRandomString(8);
    $pass = generateRandomString(20);

    try {
        if (isset($_SESSION['login'])) {
            $stmt = $db->prepare("SELECT user_id FROM user_login WHERE login = ?");
            $stmt->execute([$_SESSION['login']]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user_data && isset($user_data['user_id'])) {
                $user_id = $user_data['user_id'];

                $stmt = $db->prepare("
                    UPDATE user 
                    SET fio = ?, 
                        tel = ?, 
                        email = ?, 
                        bdate = ?, 
                        gender = ?, 
                        bio = ?, 
                        ccheck = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['fio'],
                    $_POST['tel'],
                    $_POST['email'],
                    $_POST['bdate'],
                    $_POST['radio'],
                    $_POST['bio'],
                    isset($_POST["ccheck"]) ? 1 : 0,
                    $user_id
                ]);

                $stmt = $db->prepare("DELETE FROM user_language WHERE user_id = ?");
                $stmt->execute([$user_id]);

                if (isset($_POST['abilities']) && is_array($_POST['abilities'])) {
                    $stmt = $db->prepare("INSERT INTO user_language (user_id, lang_id) VALUES (?, ?)");
                    foreach ($_POST['abilities'] as $ability) {
                        $stmt->execute([$user_id, $ability]);
                    }
                }
            }
        } else {
            $stmt = $db->prepare("INSERT INTO user (fio, tel, email, bdate, gender, bio, ccheck) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['fio'], $_POST['tel'], $_POST['email'], $_POST['bdate'], $_POST['radio'], $_POST['bio'], isset($_POST["ccheck"]) ? 1 : 0]);

            $user_id = $db->lastInsertId();

            $stmt = $db->prepare("INSERT INTO user_login (user_id, login, password) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $login, password_hash($pass, PASSWORD_DEFAULT)]);

            if (isset($_POST['abilities']) && is_array($_POST['abilities'])) {
                $stmt = $db->prepare("INSERT INTO user_language (user_id, lang_id) VALUES (?, ?)");
                foreach ($_POST['abilities'] as $ability) {
                    $stmt->execute([$user_id, $ability]);
                }
            }

            setcookie('login', $login, time() + 3600);
            setcookie('pass', $pass, time() + 3600);
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage()); // Запись в лог
        print ('Ошибка при работе с базой данных. Пожалуйста, попробуйте позже.');
        exit();
    }


    setcookie('save', '1', time() + 3600);
    header('Location: index.php');
    exit();
}

function getErrorMessage($field, $errorCode)
{
    $message = '';
    switch ($field) {
        case 'fio':
            if ($errorCode == '1') {
                $message = '<div class="error">Введите ФИО.</div>';
            } elseif ($errorCode == '2') {
                $message = '<div class="error">ФИО не должно превышать 150 символов.</div>';
            } elseif ($errorCode == '3') {
                $message = '<div class="error">ФИО должно содержать только буквы и пробелы.</div>';
            } else {
                $message = '<div class="error">ФИО содержит недопустимые символы.</div>';
            }
            break;
        case 'tel':
            $message = '<div class="error">Введите корректный номер телефона.</div>';
            break;
        case 'email':
            $message = '<div class="error">Введите корректный email.</div>';
            break;
        case 'abilities':
            $message = '<div class="error">Выберите любимый язык программирования.</div>';
            break;
        case 'bdate':
            $message = '<div class="error">Введите корректную дату рождения.</div>';
            break;
        case 'radio':
            $message = '<div class="error">Выберите пол.</div>';
            break;
        case 'bio':
            if ($errorCode == '1') {
                $message = '<div class="error">Заполните биографию.</div>';
            } elseif ($errorCode == '2') {
                $message = '<div class="error">Количество символов в поле "биография" не должно превышать 512.</div>';
            } elseif ($errorCode == '3') {
                $message = '<div class="error">Поле "биография" содержит недопустимые символы.</div>';
            }
            break;
        case 'ccheck':
            $message = '<div class="error">Подтвердите ознакомление с контрактом.</div>';
            break;
        default:
            $message = '<div class="error">Неизвестная ошибка.</div>';
            break;
    }
    return $message;
}
?>
