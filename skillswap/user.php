<?php
session_start();
include('./includes/db.php'); // Подключение к БД
include('./includes/functions.php');

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Получение информации о пользователе
$user = get_user_data($conn, $user_id);

// Получение навыков пользователя
$user_skills = get_user_skills($conn, $user_id);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Профиль пользователя</title>
    <link rel="stylesheet" href="./style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
    <!-- Шапка -->
    <nav class="mainNav">
        <div class="mainNav__logo">SkillSwap</div>
        <div class="mainNav__links">
            <a href="index.php" class="mainNav__link">Главная страница</a>
            <a href="logout.php" class="mainNav__link">Выход</a>
        </div>
    </nav>

    <div class="container">
        <h2>Профиль пользователя</h2>

        <?php if ($user): ?>
            <p><strong>Логин:</strong> <?php echo htmlspecialchars($user['login']); ?></p>
            <p><strong>Имя:</strong> <?php echo htmlspecialchars($user['first_name']); ?></p>
            <p><strong>Фамилия:</strong> <?php echo htmlspecialchars($user['last_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Дата рождения:</strong> <?php echo htmlspecialchars($user['birthdate']); ?></p>
            <p><strong>Пол:</strong> <?php echo htmlspecialchars($user['gender']); ?></p>
        <?php else: ?>
            <p>Ошибка: Не удалось получить информацию о пользователе.</p>
        <?php endif; ?>

        <h3>Ваши навыки:</h3>
        <?php if ($user_skills): ?>
            <ul>
                <?php foreach ($user_skills as $skill): ?>
                    <li>
                        <?php echo htmlspecialchars($skill['name']); ?> - <?php echo htmlspecialchars($skill['description']); ?>
                        <form action="actions/delete_skill.php" method="post" style="display: inline;">
                            <input type="hidden" name="skill_id" value="<?php echo $skill['skills_id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>У вас пока нет добавленных навыков.</p>
        <?php endif; ?>

        <a href="edit_profile.php" class="btn btn-primary">Редактировать профиль</a>
    </div>
</body>
</html>
