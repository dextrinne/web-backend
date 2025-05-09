<?php
// Соединение с базой данных
$user = 'u68595';
$pass = '6788124';
try {
    $db = new PDO(
        'mysql:host=localhost;dbname=u68595',
        $user,
        $pass,
        [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    error_log('Database connection error: ' . $e->getMessage());
    die('Ошибка подключения к базе данных.');
}
