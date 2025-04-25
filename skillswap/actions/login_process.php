<?php
session_start();
include('../includes/db.php');
include('../includes/functions.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = sanitize_input($_POST['login']);
    $password = $_POST['password']; // Пароль не нужно "санитизировать", но необходима валидация!

    // Проверяем, существует ли пользователь с таким логином
    $stmt = $conn->prepare("SELECT user_id, password FROM users_p WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Проверяем пароль
        if (password_verify($password, $user['password'])) {
            // Аутентификация успешна
            $_SESSION['user_id'] = $user['user_id'];
            header("Location: ../index.php"); // Перенаправляем на главную страницу
            exit();
        } else {
            // Неверный пароль
            echo "Неверный пароль.";
        }
    } else {
        // Пользователь не найден
        echo "Пользователь с таким логином не найден.";
    }
} else {
    // Если обратились к файлу не через POST-запрос
    header("Location: ../login.php");
    exit();
}
?>
