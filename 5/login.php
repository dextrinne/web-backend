<?php
header('Content-Type: text/html; charset=UTF-8');

// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check for logout
if (!empty($_GET['exit'])) {
    session_destroy();  // Destroy the session
    header('Location: index.php'); // Redirect to the main page
    exit();
}

// Check if the user is already logged in
if (isset($_SESSION['login'])) {
    header('Location: index.php');  // Redirect to index.php if already logged in
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
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
                    <input name="login" />
                    <p>Пароль:</p>
                    <input name="pass" type="password" /><br>
                    <input class="button" id="button" type="submit" value="ВОЙТИ" />
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
} else {
    $user = 'u68595';
    $pass = '6788124';
    try {
        $db = new PDO(
            "mysql:host=localhost;dbname=u68595",
            $user,
            $pass,
            [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $login = $_POST['login'];
        $password = $_POST['pass'];

        $stmt = $db->prepare("SELECT user_id, password FROM user_login WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['login'] = $login; 
            header('Location: index.php');
            exit();
        } else {
            $error_message = "Неверный логин или пароль";
            echo "<script>alert('$error_message'); window.location.href='login.php';</script>";
        }
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>
