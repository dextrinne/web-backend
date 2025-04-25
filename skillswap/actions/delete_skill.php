<?php
session_start();
include('../includes/db.php');
include('../includes/functions.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Проверка, авторизован ли пользователь и передан ли ID навыка
    if (!isset($_SESSION['user_id']) || !isset($_POST['skill_id'])) {
        header("Location: ../index.php"); // Перенаправляем на главную, если что-то не так
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $skill_id = $_POST['skill_id'];

    // Удаляем навык пользователя
    if (delete_user_skill($conn, $user_id, $skill_id)) {
        // Успешно удалено
        header("Location: ../user.php?skill_deleted=true");
        exit();
    } else {
        // Ошибка при удалении
        echo "Ошибка при удалении навыка.";
    }
} else {
    // Если обратились к файлу не через POST-запрос
    header("Location: ../user.php");
    exit();
}
?>
