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
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>

<style>
    /* ----------- Общее ----------- */
    * {
        margin: 0;
        padding: 0;
        list-style: none;
        border: 0;
        outline: 0;
        -webkit-tap-highlight-color: transparent;
        text-decoration: none;
        box-sizing: border-box;
    }

    body {
        font-family: "Raleway", sans-serif;
        background: linear-gradient(to top, #588157 0%, #A3B18A 100%);
        height: auto;
        background-attachment: fixed;
        background-size: cover;
    }

    /* ----------- Шапка ----------- */
    header{
        margin-bottom: 10vh;
    }
    .mainNav {
        width: 100%;
        height: 80px;
        position: absolute;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #2e1c0d;
        text-transform: uppercase;
        padding: 0 40px;
    }

    .mainNav__logo {
        font-weight: 800;
        letter-spacing: 1px;
        font-size: 18px;
    }

    .mainNav__links {
        display: flex;
        color: #2e1c0d;
    }

    .mainNav__link {
        color: #2e1c0d;
        letter-spacing: 1px;
        font-size: 14px;
        margin-left: 20px;
        font-weight: 600;
        box-shadow: inset 0px -10px 0px rgba(255, 255, 255, 0.5);
        transition: all 0.4s ease, transform 0.2s ease;
        padding: 2px 4px;
        transform: translateY(0px);
    }

    .mainNav__link:hover {
        color: #2e1c0d;
        transform: translateY(-5px);
        box-shadow: inset 0px -20px 0px #DAD7CD;
    }

    /* ----------- Контейнер профиля ----------- */
    .container {
        max-width: 800px;
        margin: 100px auto 50px;
        padding: 30px;
        background: rgba(255, 255, 255, 0.2);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        animation: fadeIn 0.6s ease-out;
        color: #2e1c0d;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    h2 {
        color: #3A5A40;
        margin-bottom: 25px;
        padding-bottom: 10px;
        border-bottom: 2px solid #A3B18A;
        font-weight: 700;
    }

    h3 {
        color: #3A5A40;
        margin: 25px 0 15px;
        font-weight: 600;
    }

    p {
        color: #2e1c0d;
    }

    /* ----------- Информация о пользователе ----------- */
    .profile-info {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .profile-info p {
        background-color: #2e1c0d;
        padding: 12px 15px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .profile-info p:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .profile-info strong {
        color: #344E41;
        font-weight: 600;
    }

    /* ----------- Список навыков ----------- */
    .skills-list {
        display: grid;
        gap: 15px;
    }

    .skill-item {
        background-color: #F8F9FA;
        padding: 15px 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
    }

    .skill-item:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .skill-text {
        flex: 1;
    }

    .skill-name {
        font-weight: 600;
        color: #3A5A40;
    }

    .skill-description {
        color: #6C757D;
        font-size: 14px;
        margin-top: 5px;
    }

    /* ----------- Формы ----------- */
    .form-group {
        margin-bottom: 20px;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #588157;
        background-color: #588157;
        transition: all 0.3s ease;
        font-family: "Raleway", sans-serif;
    }

    .form-control:focus {
        border-color: #A3B18A;
        box-shadow: 0 0 0 3px rgba(163, 177, 138, 0.2);
        background-color: white;
    }

    textarea.form-control {
        min-height: 100px;
    }

    /* ----------- Кнопки ----------- */
    .btn {
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
        font-family: "Raleway", sans-serif;
        letter-spacing: 0.5px;
    }

    .btn-primary {
        background-color: #3A5A40;
        color: white;
        border: 2px solid #3A5A40;
    }

    .btn-primary:hover {
        background-color: #344E41;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(58, 90, 64, 0.3);
    }

    .btn-success {
        background-color: #588157;
        color: white;
        border: 2px solid #588157;
    }

    .btn-success:hover {
        background-color: #4A6F48;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(88, 129, 87, 0.3);
    }

    .btn-danger {
        background-color: #E07A5F;
        color: white;
        border: 2px solid #E07A5F;
    }

    .btn-danger:hover {
        background-color: #D1664F;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(224, 122, 95, 0.3);
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 13px;
    }

</style>

<body>
    <!-- Шапка -->
    <header>
    <nav class="mainNav">
        <div class="mainNav__logo">SkillSwap</div>
        <div class="mainNav__links">
            <a href="index.php" class="mainNav__link">Главная страница</a>
            <a href="index.php" class="mainNav__link">Выход</a>
        </div>
    </nav>
    </header>

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

        <!-- Форма для добавления навыка -->
        <h3>Добавить новый навык:</h3>
        <form action="actions/add_skill.php" method="post">
            <div class="form-group">
                <label for="skill_name">Название навыка:</label>
                <input type="text" class="form-control" id="skill_name" name="skill_name" required>
            </div>
            <div class="form-group">
                <label for="skill_description">Описание навыка:</label>
                <textarea class="form-control" id="skill_description" name="skill_description"
                    style="resize: vertical;"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Добавить навык</button>
        </form>

        <a href="edit_profile.php" class="btn btn-primary">Редактировать профиль</a>
    </div>
</body>

</html>
