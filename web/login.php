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
            header("Location: /web-backend/web/#form-anchor");
            exit();
        } else {
            $error_message = "Неверный логин или пароль.";
        }
    } catch (PDOException $e) {
        $error_message = "Ошибка базы данных: " . $e->getMessage();
    }
}
?>