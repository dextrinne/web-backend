<?php
session_start();
include('../includes/db.php');
include('../includes/functions.php');

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Собираем и "санитизируем" данные из формы
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $birthdate = sanitize_input($_POST['birthdate']);
    $gender = sanitize_input($_POST['gender']);

    try {
        // Подготавливаем запрос на обновление данных пользователя
        $stmt = $conn->prepare("
            UPDATE users_p
            SET first_name = ?, last_name = ?, email = ?, birthdate = ?, gender = ?
            WHERE user_id = ?
        ");

        // Выполняем запрос, передавая параметры
        $stmt->execute([$first_name, $last_name, $email, $birthdate, $gender, $user_id]);

        // Перенаправляем пользователя на страницу профиля с сообщением об успехе
        header("Location: ../user.php?success=true");
        exit();

    } catch (Exception $e) {
        // В случае ошибки выводим сообщение об ошибке
        echo "Ошибка при обновлении профиля: " . $e->getMessage();
    }

} else {
    // Если обратились к файлу не через POST-запрос, перенаправляем на страницу профиля
    header("Location: ../user.php");
    exit();
}
?>
