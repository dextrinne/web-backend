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

// Получение навыков других пользователей для карусели
$other_users_skills = get_other_users_skills($conn, $user_id);

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Профиль пользователя</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>


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
    header {
        margin-bottom: 10vh;
    }

    .mainNav {
        width: 100%;
        height: 80px;
        position: flex;
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
        margin-top: 15pt;
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
        background: rgba(255, 255, 255, 0.2);
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

    /* ----------- Карусель навыков ----------- */
    .carousel {
        position: static;
    }

    .carousel-inner {
        position: relative;
        width: 100%;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.2);
        text-align: center
    }

    .carousel-item {
        display: none;
        position: relative;
        width: 100%;
        transition: transform 0.6s ease;
        backface-visibility: hidden;
        perspective: 1000px;
    }

    .carousel-item.active {
        display: block;
    }

    .carousel-control-prev,
    .carousel-control-next {
        position: absolute;
        top: 0;
        bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 15%;
        color: #fff;
        text-align: center;
        opacity: 0.5;
    }

    .carousel-control-prev {
        left: 0;
    }

    .carousel-control-next {
        right: 0;
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        display: inline-block;
        width: 20px;
        height: 20px;
        background: no-repeat 50%/100% 100%;
    }

    .carousel-control-prev-icon {
        background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%233A5A40' viewBox='0 0 8 8'%3E%3Cpath d='M5.25 0l-4 4 4 4 1.5-1.5-2.5-2.5 2.5-2.5-1.5-1.5z'/%3E%3C/svg%3E");
    }

    .carousel-control-next-icon {
        background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%233A5A40' viewBox='0 0 8 8'%3E%3Cpath d='M2.75 0l-1.5 1.5 2.5 2.5-2.5 2.5 1.5 1.5 4-4-4-4z'/%3E%3C/svg%3E");
    }

    /* Стили для отображения добавленных навыков */
    .added-skills {
        margin-top: 30px;
        padding: 20px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }

    .added-skill-item {
        background: white;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .added-from {
        font-size: 12px;
        color: #6C757D;
        font-style: italic;
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
            <div class="profile-info">
                <p><strong>Логин:</strong> <?php echo htmlspecialchars($user['login']); ?></p>
                <p><strong>Имя:</strong> <?php echo htmlspecialchars($user['first_name']); ?></p>
                <p><strong>Фамилия:</strong> <?php echo htmlspecialchars($user['last_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Дата рождения:</strong> <?php echo htmlspecialchars($user['birthdate']); ?></p>
                <p><strong>Пол:</strong> <?php echo htmlspecialchars($user['gender']); ?></p>
            </div>
        <?php else: ?>
            <p>Ошибка: Не удалось получить информацию о пользователе.</p>
        <?php endif; ?>

        <!-- Карусель навыков других пользователей -->
        <h2>Каталог навыков других пользователей:</h2>
        <div id="skillsCarousel" class="carousel slide" data-ride="carousel">
            <!-- Индикаторы -->
            <ol class="carousel-indicators">
                <?php for ($i = 0; $i < count($other_users_skills); $i++): ?>
                    <li data-target="#skillsCarousel" data-slide-to="<?= $i ?>" <?= $i === 0 ? 'class="active"' : '' ?>></li>
                <?php endfor; ?>
            </ol>

            <!-- Слайды -->
            <div class="carousel-inner">
                <?php foreach ($other_users_skills as $index => $skill): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <div class="skill-card">
                            <h4><?= htmlspecialchars($skill['name']) ?></h4>
                            <p class="skill-meta">
                                <?= htmlspecialchars($skill['first_name'] . ' ' . $skill['last_name']) ?><br>
                                <?= htmlspecialchars($skill['gender']) ?><br>
                                <?= htmlspecialchars($skill['email']) ?>
                            </p>
                            <p><?= htmlspecialchars($skill['description']) ?></p>
                            <form action="actions/add_skill_from_user.php" method="post" style="margin-top: 15px;">
                                <input type="hidden" name="skill_id" value="<?= $skill['skills_id'] ?>">
                                <input type="hidden" name="from_user_id" value="<?= $skill['user_id'] ?>">
                                <button type="submit" class="btn btn-success">Добавить себе</button>
                            </form>
                            <?php if ($skill['has_multiple']): ?>
                                <form action="actions/add_skill_from_user.php" method="post" style="margin-top: 10px;">
                                    <input type="hidden" name="skill_id" value="<?= $skill['skills_id'] ?>">
                                    <input type="hidden" name="from_user_id" value="<?= $skill['user_id'] ?>">
                                    <input type="hidden" name="add_all" value="1">
                                    <button type="submit" class="btn btn-info">Добавить все навыки</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Элементы управления -->
            <a class="carousel-control-prev" href="#skillsCarousel" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Предыдущий</span>
            </a>
            <a class="carousel-control-next" href="#skillsCarousel" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Следующий</span>
            </a>
        </div>

        <!-- Отображение добавленных навыков -->
        <h2>Ваши добавленные навыки:</h2>
        <div class="added-skills">
            <?php if ($user_skills): ?>
                <?php foreach ($user_skills as $skill): ?>
                    <div class="added-skill-item">
                        <div class="skill-name"><?php echo htmlspecialchars($skill['name']); ?></div>
                        <div class="skill-description"><?php echo htmlspecialchars($skill['description']); ?></div>
                        <?php if (isset($skill['added_from_user_id'])): ?> <!-- Проверяем, существует ли ключ -->
                            <?php
                            $added_from_user = get_user_data($conn, $skill['added_from_user_id']);
                            if ($added_from_user): ?>
                                <div class="added-from">Добавлено от:
                                    <?php echo htmlspecialchars($added_from_user['first_name'] . ' ' . $added_from_user['last_name']); ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <form action="actions/delete_skill.php" method="post" style="display: inline-block; margin-top: 10px;">
                            <input type="hidden" name="skill_id" value="<?php echo $skill['skills_id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>У вас пока нет добавленных навыков.</p>
            <?php endif; ?>
        </div>


        <!-- Форма для добавления навыка -->
        <h2>Добавить новый навык:</h2>
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
    <script>
        $(document).ready(function () {
            // Инициализация карусели
            $('#skillsCarousel').carousel({
                interval: false, // Отключаем автоматическое перелистывание
                wrap: true // Позволяем бесконечное перелистывание
            });

            // Обработчики для кнопок
            $('.carousel-control-prev').click(function () {
                $('#skillsCarousel').carousel('prev');
            });

            $('.carousel-control-next').click(function () {
                $('#skillsCarousel').carousel('next');
            });
        });
    </script>
</body>

</html>
