<?php
function auth(&$request, $r) {
    global $db;

    // Если нет данных авторизации — запрашиваем
    if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="Admin Area"');
        die("Требуется авторизация."); // Останавливаем выполнение
    }

    // Проверяем логин/пароль
    $admin_login = $_SERVER['PHP_AUTH_USER'];
    $admin_password = $_SERVER['PHP_AUTH_PW'];

    try {
        $stmt = $db->prepare("SELECT id, password FROM admin WHERE login = ?");
        $stmt->execute([$admin_login]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Если пароль неверный — снова запрашиваем
        if (!$admin || !hash_equals($admin['password'], hash('sha256', $admin_password))) {
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Basic realm="Admin Area"');
            die("Неверные учетные данные.");
        }

        // Успешная авторизация
        return true; 
    } catch (PDOException $e) {
        error_log("Ошибка базы данных: " . $e->getMessage());
        die("Ошибка сервера.");
    }
}
?>