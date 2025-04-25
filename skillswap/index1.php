<?php
// Подключение к базе данных
$host = 'localhost';
$dbname = 'u68595';
$username = 'u68595';
$password = '6788124';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Запрос для получения данных пользователей с их навыками
    $query = "
        SELECT 
            u.user_id,
            u.first_name,
            u.last_name,
            u.gender,
            u.email,
            s.skills_id,
            s.name AS skill_name,
            s.description AS skill_description
        FROM 
            users_p u
        JOIN 
            user_skills us ON u.user_id = us.user_id
        JOIN 
            skills s ON us.skill_id = s.skills_id
        ORDER BY 
            u.last_name, u.first_name, s.name
    ";

    $stmt = $pdo->query($query);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пользователи и их навыки - Карусель</title>
    <!-- Подключаем Slick CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .user-carousel {
            margin: 0 auto;
            width: 90%;
        }
        .user-slide {
            padding: 10px;
            outline: none;
        }
        .user-block {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            margin: 0 10px;
            height: 100%;
            transition: transform 0.3s ease;
        }
        .user-block:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .user-info {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .user-name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .user-details {
            color: #666;
        }
        .skill-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .skill-name {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .skill-description {
            color: #555;
            font-size: 14px;
        }
        .gender-male {
            color: #3498db;
        }
        .gender-female {
            color: #e91e63;
        }
        .slick-prev:before, 
        .slick-next:before {
            color: #333;
        }
        .slick-dots li button:before {
            font-size: 12px;
        }
        .error-message {
            color: #d9534f;
            padding: 15px;
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Наши пользователи и их навыки</h1>
        
        <?php if (isset($users)): ?>
            <div class="user-carousel">
                <?php
                $currentUserId = null;
                foreach ($users as $user) {
                    // Если это новый пользователь, начинаем новый слайд
                    if ($user['user_id'] !== $currentUserId) {
                        // Закрываем предыдущий слайд, если он был
                        if ($currentUserId !== null) {
                            echo '</div></div>'; // закрываем .skill-info и .user-block
                        }
                        
                        $currentUserId = $user['user_id'];
                        $genderClass = strtolower($user['gender']) === 'male' ? 'gender-male' : 'gender-female';
                        
                        echo '<div class="user-slide"><div class="user-block">';
                        echo '<div class="user-info">';
                        echo '<div class="user-name">' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '</div>';
                        echo '<div class="user-details">';
                        echo '<span class="' . $genderClass . '">' . htmlspecialchars($user['gender']) . '</span>';
                        echo ' | ' . htmlspecialchars($user['email']);
                        echo '</div>';
                        echo '</div>'; // закрываем .user-info
                    }
                    
                    // Выводим информацию о навыке
                    echo '<div class="skill-info">';
                    echo '<div class="skill-name">' . htmlspecialchars($user['skill_name']) . '</div>';
                    echo '<div class="skill-description">' . htmlspecialchars($user['skill_description']) . '</div>';
                    echo '</div>'; // закрываем .skill-info
                }
                
                // Закрываем последний слайд, если есть пользователи
                if (!empty($users)) {
                    echo '</div></div>'; // закрываем .user-block и .user-slide
                } else {
                    echo '<div class="error-message">Нет данных о пользователях и их навыках.</div>';
                }
                ?>
            </div>
        <?php else: ?>
            <div class="error-message">
                Не удалось загрузить данные пользователей. Пожалуйста, проверьте подключение к базе данных.
            </div>
        <?php endif; ?>
    </div>

    <!-- Подключаем jQuery (необходим для Slick) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Подключаем Slick JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    
    <script>
        $(document).ready(function(){
            $('.user-carousel').slick({
                dots: true,
                infinite: true,
                speed: 300,
                slidesToShow: 3,
                slidesToScroll: 1,
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 1,
                            infinite: true,
                            dots: true
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                    }
                ]
            });
        });
    </script>
</body>
</html>
