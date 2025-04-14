<?php
header('Content-Type: text/html; charset=UTF-8');// Отправляем браузеру правильную кодировку, файл login.php должен быть в кодировке UTF-8 без BOM.

// Сохраним суперглобальный массив $_SESSION в переменные сессии логин после успешной авторизации.
$session_started = false;
if ($_COOKIE[session_name()] && session_start()) {
    $session_started = true;
    if (!empty($_GET['exit'])) {
        // выход (окончание сессии session_destroy() при нажатии на кнопку Выход).
        session_destroy();
        header('Location: index.php');
        exit();
    }
    if (!empty($_SESSION['hasLogged']) && $_SESSION['hasLogged'] = true) {
        // Если есть логин в сессии, то пользователь уже авторизован, перенаправляем на форму.
        header('Location: ./');
        exit();
    }
}

// и другие сведения о клиненте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Основной вид страницы
    ?>
    <html>
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Вход</title>
        </head>

        <body>
            <div id="form" class="form">
                <form action="" method="post">
                    <h2>ВХОД В АККАУНТ</h2>
                    <form action="" method="post">
                        <p>Логин:</p>
                        <input name="login"/>
                        <p>Пароль:</p>
                        <input name="pass"/><br>
                        <input class="button" id="button" type="submit" value="ВОЙТИ"/>
                    </form>
                </form>
            </div>
        </body>

        <style>
            body {
                color: #006a71;
                background: #cbeaed;
                font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;   
                padding: 8% 0 0;
                margin: auto;
            }

            p{
                text-align: left;
                font-size: 16pt;
                margin-top: 0pt;
                padding-top: 0pt;
            }

            .button {
                outline: 0;
                background: #006a71;
                width: 100%;
                border: 0;
                margin: 0 0 15px;
                padding: 15px;
                box-sizing: border-box;
                font-size: 14px;
                color: white;
            }

            input[type="submit"]:hover {
                background-color: #034e54;
            }

            .form {
                position: relative;
                z-index: 1;
                background: #FFFFFF;
                max-width: 360px;
                margin: 0 auto 100px;
                padding: 45px;
                text-align: center;
                box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.2), 0 5px 5px 0 rgba(0, 0, 0, 0.24);
            }

            .form input[name="login"],
            .form input[name="pass"] {
                color: #034e54;
                outline: 0;
                background: #cbeaed;
                width: 100%;
                border: 0;
                margin: 0 0 15px;
                padding: 15px;
                box-sizing: border-box;
                font-size: 14px;
            }
        </style>
      </html>
    <?php
}

// Иначе, если запрос был методом POST, т.е. нужно сделать авторизацию с записью логина в сессию.
else {
    include ('../Secret.php');
    $user = userr;
    $pass = passs;
    $db = new PDO(
        "mysql:host=localhost;dbname=$user",
        $user,
        $pass,
        [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    // проверка наличия логина в базе данных
    $loginFlag = false;
    try {
        $select = "SELECT * FROM Logi";
        $result = $db->query($select);
        if (!$session_started) {
            session_start();
        }
        while ($row = $result->fetch()) {
            if ($_POST['login'] == $row['login'] && md5($_POST['pass']) == $row['password']) {
                $loginFlag = true;
                break;
            }
        }
    } catch (PDOException $e) {
        setcookie('DBERROR', 'Error : ' . $e->getMessage());
        exit();
    }

    // Если все ок, то авторизуем пользователя.
    if ($loginFlag) {
        $_SESSION['hasLogged'] = true;
        $_SESSION['login'] = $_POST['login'];
        $_SESSION['pass'] = $_POST['pass'];
    } else {
        $_SESSION['hasLogged'] = false;
        $_SESSION['login'] = '';
        $_SESSION['pass'] = '';
        setcookie('AUTHERROR', 'Неверный логин или пароль');
    }

    // Делаем перенаправление.
    header('Location: ./');
}
?>