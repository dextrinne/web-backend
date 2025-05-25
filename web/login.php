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
            $_SESSION["login"] = $login;
            $_SESSION["uid"] = $user["user_id"];
            header("Location: /");
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
    <title>Вход</title>
</head>

<body>
    <h2>Вход</h2>

    <?php if (isset($error_message)) : ?>
    <p style="color: red;"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="login">Логин:</label><br>
        <input type="text" id="login" name="login"><br><br>

        <label for="password">Пароль:</label><br>
        <input type="password" id="password" name="password"><br><br>

        <input type="submit" value="Войти">
    </form>
</body>

</html>