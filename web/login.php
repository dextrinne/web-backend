<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include('./settings.php');
    include('./scripts/db.php');

    $login = $_POST["login"];
    $password = $_POST["password"];

    try {
        $stmt = $db->prepare("SELECT user_id, password FROM user_login WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION['login'] = $login;
            $_SESSION['uid'] = $user['user_id'];
            $_SESSION['is_admin'] = false; 
            /*header("Location: /web-backend/web/edit_user/" . $user["user_id"]);*/
            header("Location: /web-backend/web/modules/edit_user.php?id=". $user["user_id"]);
            exit();
        } else {
            $error_message = "Неверный логин или пароль.";
        }
    } catch (PDOException $e) {
        $error_message = "Ошибка базы данных: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <style>
        body {
            color: #006a71;
            background: #cbeaed;
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
            padding: 8% 0 0;
            margin: auto;
        }

        p {
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
        .form input[name="password"] {
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
        
        .error-message {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div id="form" class="form">
        <form method="post">
            <h2>ВХОД В АККАУНТ</h2>
            <?php if (isset($error_message)) : ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <p>Логин:</p>
            <input type="text" name="login" />
            <p>Пароль:</p>
            <input type="password" name="password" /><br>
            <input class="button" id="button" type="submit" value="ВОЙТИ" />
        </form>
    </div>
</body>
</html>
