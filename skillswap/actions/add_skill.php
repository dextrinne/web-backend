<?php
session_start();
include('../includes/db.php');
include('../includes/functions.php');

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Собираем и "санитизируем" данные из формы
    $skill_name = sanitize_input($_POST['skill_name']);
    $skill_description = sanitize_input($_POST['skill_description']);

    try {
        // Начинаем транзакцию
        $conn->beginTransaction();

        // 1. Проверяем, существует ли уже такой навык
        $stmt = $conn->prepare("SELECT skills_id FROM skills WHERE name = ?");
        $stmt->execute([$skill_name]);
        $skill = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($skill) {
            // Навык уже существует
            $skill_id = $skill['skills_id'];
        } else {
            // 2. Создаем новый навык
            $stmt = $conn->prepare("INSERT INTO skills (name, description) VALUES (?, ?)");
            $stmt->execute([$skill_name, $skill_description]);
            $skill_id = $conn->lastInsertId();
        }

        // 3. Связываем пользователя с навыком
        $stmt = $conn->prepare("INSERT INTO user_skills (user_id, skill_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $skill_id]);

        // Подтверждаем транзакцию
        $conn->commit();

        // Перенаправляем обратно на страницу профиля
        header("Location: ../user.php?skill_added=true");
        exit();

    } catch (Exception $e) {
        // Откатываем транзакцию в случае ошибки
        $conn->rollBack();
        echo "Ошибка при добавлении навыка: " . $e->getMessage();
    }
} else {
    // Если обратились к файлу не через POST-запрос
    header("Location: ../user.php");
    exit();
}
?>
