<?
session_start();
include('./includes/db.php'); // Подключение к БД
include('./includes/functions.php'); // Подключение функций

// Получаем данные всех пользователей с их навыками
$users_with_skills = get_all_users_with_skills($conn);

// Организуем данные в массив, сгруппированный по пользователям
$users = []; // Инициализация массива users
if ($users_with_skills) {
    foreach ($users_with_skills as $row) {
        $user_key = $row['first_name'] . ' ' . $row['last_name'] . ' (' . $row['email'] . ')'; // Уникальный ключ для каждого пользователя
        if (!isset($users[$user_key])) {
            $users[$user_key] = [
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'email' => $row['email'],
                'gender' => $row['gender'],
                'skills' => []
            ];
        }
        $users[$user_key]['skills'][] = [
            'skill_name' => $row['skill_name'],
            'skill_description' => $row['skill_description']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>SkillSwap</title>
    <link rel="stylesheet" href="./style/style.css">
    <script src='https://cdnjs.cloudflare.com/ajax/libs/parallax/3.1.0/parallax.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/parallax/3.1.0/parallax.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="./style/script.js"></script>
</head>

<body>
    <!-- Шапка -->
    <nav class="mainNav">
        <div class="mainNav__logo">SkillSwap</div>
        <div class="mainNav__links">
            <a href="#1" class="mainNav__link">О нас</a>
            <a href="#2" class="mainNav__link">Навыки пользователей</a>
            <a href="login.php" class="mainNav__link">Вход</a>
            <a href="register.php" class="mainNav__link">Регистрация</a>
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

    <!-- Отображение пользователей из БД -->
    <div class="db_users" id="2">
        <h2>Навыки наших пользователей</h2>
        <div class="futuristic-border"></div>
        <p>
            Выберете, к чему лежит Ваша душа, из множества вариантов!
        </p>
        <!-- Карусель с данными пользователей -->
    <div id="userCarousel" class="carousel slide" data-ride="carousel">
        <!-- Индикаторы (точки внизу карусели) -->
        <ol class="carousel-indicators">
            <?php
            $i = 0;
            if(!empty($users)):
                foreach ($users as $user_key => $user): ?>
                    <li data-target="#userCarousel" data-slide-to="<?php echo $i; ?>" <?php if ($i == 0) echo 'class="active"'; ?>></li>
                    <?php $i++; ?>
                <?php endforeach;
            endif; ?>
        </ol>

        <!-- Слайды -->
        <div class="carousel-inner" role="listbox">
            <?php
            $i = 0;
            if(!empty($users)):
                foreach ($users as $user_key => $user): ?>
                    <div class="item <?php if ($i == 0) echo 'active'; ?>">
                        <div class="carousel-caption">
                            <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                            <p><strong>Пол:</strong> <?php echo htmlspecialchars($user['gender']); ?></p>
                            <h4>Навыки:</h4>
                            <?php if (!empty($user['skills'])): ?>
                                <ul>
                                    <?php foreach ($user['skills'] as $skill): ?>
                                        <li>
                                            <strong><?php echo htmlspecialchars($skill['skill_name']); ?>:</strong>
                                            <?php echo htmlspecialchars($skill['skill_description']); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>Нет навыков.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php $i++; ?>
                <?php endforeach;
            endif; ?>
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
    </div>

    <footer>© Copyright 2025 - SkillSwap. All rights reserved.</footer>
</body>

</html>
