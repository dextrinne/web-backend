
<?
session_start();
include('./includes/db.php');
include('./includes/functions.php');

// Получаем данные всех пользователей с их навыками
$users_with_skills = get_all_users_with_skills($conn);

?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>SkillSwap</title>
    <link rel="stylesheet" href="./style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
    <script src='https://cdnjs.cloudflare.com/ajax/libs/parallax/3.1.0/parallax.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/parallax/3.1.0/parallax.js'></script>
    <script src="./style/script.js"></script>
    <style>
        .carousel-caption {
            background-color: rgba(0, 0, 0, 0.5); /* Полупрозрачный фон для caption */
            padding: 20px;
            border-radius: 10px;
        }

        .carousel-caption h3 {
            color: white;
        }

        .carousel-caption p {
            color: white;
        }
    </style>
</head>

<body>
    <!-- Шапка -->
    <nav class="mainNav">
        <div class="mainNav__logo">SkillSwap</div>
        <div class="mainNav__links">
            <a href="#1" class="mainNav__link">О нас</a>
            <a href="#2" class="mainNav__link">Навыки пользователей</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="user.php" class="mainNav__link">Профиль</a>
                <a href="logout.php" class="mainNav__link">Выход</a>
            <?php else: ?>
                <a href="login.php" class="mainNav__link">Вход</a>
                <a href="register.php" class="mainNav__link">Регистрация</a>
            <?php endif; ?>
        </div>
    </nav>
    <header class="mainHeading">
        <div class="mainHeading__content">
            <article class="mainHeading__text">
                <h2 class="mainHeading__title">Платформа обмена навыками</h2>
                <p class="mainHeading__description">
                    Наша платформа – это живое и динамичное общение, где абсолютно любой человек может стать как учеником,
                    так и учителем.
                    На данной платформе можно найти курсы по разным интересующим направлениям, от базовых до
                    профессиональных.
                </p>
            </article>

            <figure class="mainHeading__image"><img src="./style/teamwork.jpg" alt="SkillSwap" /></figure>
        </div>
    </header>

    <!-- О нас -->
    <section class="section1">
        <div class="container" id="1">
            <h2>Сплочаем людей</h2>
            <div class="futuristic-border"></div>
            <p>
                Платформа для обмена навыками между пользователями представляет собой инновационное решение, которое
                позволяет людям делиться своими знаниями и умениями,
                обеспечивая при этом равные возможности для всех участников. Пользователи могут создавать профили,
                описывать свои навыки и предлагать их другим пользователям
                в обмен на помощь в тех областях, где они сами нуждаются в развитии.
            </p>

            <div class="services">
                <div class="service-item">
                    <h3>Люди ищут бесплатные способы развиваться</h3>
                    <p>Люди стремятся к самосовершенствованию, находя бесплатные способы обучения и развития через обмен
                        знаниями и навыками в сообществе.</p>
                </div>
                <div class="service-item">
                    <h3>Растёт спрос на обучение и навыки</h3>
                    <p>Растет спрос на обучение и развитие навыков, что делает платформу для обмена навыками особенно
                        актуальной.</p>
                </div>
                <div class="service-item">
                    <h3>Саморазвитие становится популярным</h3>
                    <p>Саморазвитие становится популярным благодаря доступности онлайн-платформ, что позволяет людям
                        обучаться новым навыкам без финансовых затрат.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2>Никаких ограничений</h2>
            <div class="futuristic-border"></div>
            <p>
                Мы создаём уникальное сообщество, где каждый может найти поддержку и наставничество,
                независимо от своего социального статуса или финансового положения. Система рейтингов и отзывов
                помогает поддерживать высокий уровень доверия и качества предоставляемых услуг,
                делая платформу надежным и привлекательным местом для обмена знаниями и опытом.
            </p>

            <div class="services">
                <div class="service-item">
                    <h3>Индивидуальный подход</h3>
                    <p>Настраивайте программу обучения, выбирая навыки и менторов, которые соответствуют вашим
                        уникальным потребностям.
                        Учитесь у тех, кто вам подходит, в удобном для вас темпе, подстраивая программу под свои цели.
                    </p>
                </div>
                <div class="service-item">
                    <h3>Удобства для каждого</h3>
                    <p>Недостаток времени является одним из основных препятствий на пути к саморазвитию. Наша
                        платформа решает эту проблему, предоставляя возможность обучения в удобное для пользователя
                        время и в любом месте.
                    </p>
                </div>
                <div class="service-item">
                    <h3>Добровольная основа</h3>
                    <p>Высокая стоимость обучения является основным барьером для многих людей,
                        ограничивая их доступ к знаниям и возможностям развития. На нашей платформе Вы можете стать как
                        учеником, так и учителем без вложения денежных ресурсов.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Карусель с данными пользователей -->
    <div id="userCarousel" class="carousel slide" data-ride="carousel">
        <!-- Индикаторы (точки внизу карусели) -->
        <ol class="carousel-indicators">
            <?php if ($users_with_skills): ?>
                <?php for ($i = 0; $i < count($users_with_skills); $i++): ?>
                    <li data-target="#userCarousel" data-slide-to="<?php echo $i; ?>" <?php if ($i == 0) echo 'class="active"'; ?>></li>
                <?php endfor; ?>
            <?php endif; ?>
        </ol>

        <!-- Слайды -->
        <div class="carousel-inner" role="listbox">
            <?php if ($users_with_skills): ?>
                <?php $i = 0; ?>
                <?php foreach ($users_with_skills as $user): ?>
                    <div class="item <?php if ($i == 0) echo 'active'; ?>">
                        <div class="carousel-caption">
                            <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                            <p><strong>Пол:</strong> <?php echo htmlspecialchars($user['gender']); ?></p>
                            <p><strong>Навык:</strong> <?php echo htmlspecialchars($user['skill_name']); ?></p>
                            <p><strong>Описание навыка:</strong> <?php echo htmlspecialchars($user['skill_description']); ?></p>
                        </div>
                    </div>
                    <?php $i++; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="item active">
                    <div class="carousel-caption">
                        <h3>Нет доступных навыков</h3>
                        <p>Пожалуйста, добавьте пользователей и их навыки в базу данных.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Элементы управления (стрелки) -->
        <a class="left carousel-control" href="#userCarousel" role="button" data-slide="prev">
            <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
            <span class="sr-only">Предыдущий</span>
        </a>
        <a class="right carousel-control" href="#userCarousel" role="button" data-slide="next">
            <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
            <span class="sr-only">Следующий</span>
        </a>
    </div>

    <!-- Отображение пользователей из БД -->
    <div class="db_users" id="2">
        <h2>Навыки наших пользователей</h2>
        <div class="futuristic-border"></div>
        <p>
            Выберете, к чему лежит Ваша душа, из множества вариантов!
        </p>
    </div>

    <footer>© Copyright 2025 - SkillSwap. All rights reserved.</footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>

</html>
