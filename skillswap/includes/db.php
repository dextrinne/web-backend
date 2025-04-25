<?php
$username = "u68595"; // Имя пользователя БД
$password = "6788124"; // Пароль пользователя БД

try {
    $conn = new PDO('mysql:host=localhost;dbname=u68595', $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die(); // Прерываем выполнение скрипта, если нет подключения к БД
}
?>
