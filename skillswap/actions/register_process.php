<?php
session_start();
include('../includes/db.php');
include('../includes/functions.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Собираем и "санитизируем" данные из формы
    $login = sanitize_input($_POST['login']);
    $password = $_POST['password'];  // Пароль НЕ санитизируем перед хешированием
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $birthdate = sanitize_input($_POST['birthdate']);
    $gender = sanitize_input($_POST['gender']);
    $skill_name = sanitize_input($_POST['skill_name']);
    $skill_description = sanitize_input($_POST['skill_description']);

    // Хешируем пароль
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Начинаем транзакцию, чтобы гарантировать целостность данных
        $conn->beginTransaction();

        // 1. Создаем пользователя
        $stmt = $conn->prepare("
            INSERT INTO users_p (login, password, first_name, last_name, email, birthdate, gender)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([$login, $hashed_password, $first_name, $last_name, $email, $birthdate, $gender]);

        // Получаем ID созданного пользователя
        $user_id = $conn->lastInsertId();

        // 2. Создаем навык (если его еще нет) или получаем его ID
        $stmt = $conn->prepare("SELECT skills_id FROM skills WHERE name = ?");
        $stmt->execute([$skill_name]);
        $skill = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($skill) {
            // Навык уже существует
            $skill_id = $skill['skills_id'];
        } else {
            // Создаем новый навык
            $stmt = $conn->prepare("INSERT INTO skills (name, description) VALUES (?, ?)");
            $stmt->execute([$skill_name, $skill_description]);
            $skill_id = $conn->lastInsertId();
        }

        // 3. Связываем пользователя и навык
        $stmt = $conn->prepare("INSERT INTO user_skills (user_id, skill_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $skill_id]);

        // Подтверждаем транзакцию
        $conn->commit();

        // Устанавливаем сессию и перенаправляем на главную страницу
        $_SESSION['user_id'] = $user_id;
        header("Location: ../user.php");
        exit();

    } catch (Exception $e) {
        // Если произошла ошибка, откатываем транзакцию
        $conn->rollBack();
        echo "Ошибка при регистрации: " . $e->getMessage();
    }

} else {
    // Если обратились к файлу не через POST-запрос
    header("Location: ../register.php");
    exit();
}
?>
