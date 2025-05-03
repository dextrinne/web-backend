<?php
header('Content-Type: text/html; charset=UTF-8');
echo "<link rel='stylesheet' href='style.css'>";
include('./actions/db.php');
include('./actions/functions.php');
include('./actions/validation.php');

$abilities = getAbilities($db);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = array();
    
    // Проверяем, авторизован ли пользователь.
    if (isset($_SESSION['login'])) {
        $messages[] = ' Вы вошли как: ' . htmlspecialchars($_SESSION['login']);
        $logoutButton = '<a href="login.php?exit=1">Выйти</a>';
        $values = fillUserValues($db, $_SESSION['login']);
    } else {
        $logoutButton = '';
        $values = fillUserValues($db);
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

    handleValidationErrors($messages);
    echo $logoutButton; 
    include('form.php');
} else {  
    if (!validateFormData($db, $errors, $values, $abilities)) {
        header('Location: index.php');
        exit();
    }

    if ($errors) {
        header('Location: index.php');
        exit();
    } else {
        setcookie('fio_error', '', 100000);
        setcookie('tel_error', '', 100000);
        setcookie('email_error', '', 100000);
        setcookie('abilities_error', '', 100000);
        setcookie('bdate_error', '', 100000);
        setcookie('radio_error', '', 100000);
        setcookie('bio_error', '', 100000);
        setcookie('ccheck_error', '', 100000);
    }

    $login = generateRandomString(8);
    $pass = generateRandomString(20);

    try {
        if (isset($_SESSION['login'])) {
            $stmt = $db->prepare("SELECT user_id FROM user_login WHERE login = ?");
            $stmt->execute([$_SESSION['login']]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user_data && isset($user_data['user_id'])) {
                updateUserData($db, $user_data['user_id'], $_POST, $_POST['abilities']);
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
        print('Error in DB: ' . $e->getMessage());
        exit();
    }

    setcookie('save', '1', time() + 3600);  
    header('Location: index.php');
    exit();
}
?>
