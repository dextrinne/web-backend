<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include('./settings.php');
    include('./scripts/db.php');

    $login = $_POST["login"];
    $password = $_POST["password"];
    $is_admin_edit = isset($_POST["admin_edit"]);

    try {
        if ($is_admin_edit) {
            // Проверяем, что администратор авторизован
            if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SESSION['admin_login'])) {
                die("Доступ запрещен");
            }

            // Получаем данные пользователя для входа
            $stmt = $db->prepare("SELECT ul.user_id, ul.login, ul.password 
                                 FROM user_login ul
                                 JOIN user u ON ul.user_id = u.id
                                 WHERE u.email = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION["login"] = $user['login'];
                $_SESSION["uid"] = $user['user_id'];
                $_SESSION["admin_edit"] = true; // Флаг, что это редактирование от админа
                header("Location: /web-backend/web/#form-anchor");
                exit();
            }
        } else {
            // Обычный вход пользователя
            $stmt = $db->prepare("SELECT user_id, password FROM user_login WHERE login = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user["password"])) {
                $_SESSION["login"] = $login;
                $_SESSION["uid"] = $user["user_id"];
                /*header("Location: /web-backend/web/edit_user/" . $user["user_id"]);*/
                header("Location: /web-backend/web/#form-anchor");
                exit();
            } else {
                $error_message = "Неверный логин или пароль.";
            }
        }
    } catch (PDOException $e) {
        $error_message = "Ошибка базы данных: " . $e->getMessage();
    }
}
?>