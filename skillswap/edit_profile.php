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

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать профиль</title>
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
            <a href="user.php" class="mainNav__link">Профиль</a>
            <a href="logout.php" class="mainNav__link">Выход</a>
        </div>
    </nav>

    <div class="container">
        <h2>Редактировать профиль</h2>

        <?php if ($user): ?>
            <form class="form-horizontal" action="actions/update_profile.php" method="post">
                <div class="form-group">
                    <label>Имя:</label>
                    <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Фамилия:</label>
                    <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Почта:</label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Дата рождения:</label>
                    <input type="date" class="form-control" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate']); ?>">
                </div>
                <div class="form-group">
                    <label>Пол:</label>
                    <div class="radio-group">
                        <label>
                            <input class="radio" name="gender" type="radio" value="Female" <?php if($user['gender'] == 'Female') echo 'checked'; ?> />Женский
                        </label>
                        <label>
                            <input class="radio" name="gender" type="radio" value="Male" <?php if($user['gender'] == 'Male') echo 'checked'; ?> />Мужской
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-default">Сохранить изменения</button>
                </div>
            </form>
        <?php else: ?>
            <p>Ошибка: Не удалось получить информацию о пользователе.</p>
        <?php endif; ?>
    </div>
</body>
</html>
