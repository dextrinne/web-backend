<?php
session_start();
include('./includes/db.php');
include('./includes/functions.php');

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
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пользователи и их навыки</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .user-block {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
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
        }
        .user-details {
            color: #666;
            margin-top: 5px;
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
        }
        .skill-description {
            margin-top: 5px;
            color: #555;
        }
        .gender-male {
            color: #3498db;
        }
        .gender-female {
            color: #e91e63;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Пользователи и их навыки</h1>
        
        <?php
        $currentUserId = null;
        foreach ($users as $user) {
            // Если это новый пользователь, начинаем новый блок
            if ($user['user_id'] !== $currentUserId) {
                // Закрываем предыдущий блок пользователя, если он был
                if ($currentUserId !== null) {
                    echo '</div>'; // закрываем .user-block
                }
                
                $currentUserId = $user['user_id'];
                $genderClass = strtolower($user['gender']) === 'male' ? 'gender-male' : 'gender-female';
                
                echo '<div class="user-block">';
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
        
        // Закрываем последний блок пользователя, если есть пользователи
        if (!empty($users)) {
            echo '</div>'; // закрываем .user-block
        } else {
            echo '<p>Нет данных о пользователях и их навыках.</p>';
        }
        ?>
    </div>
</body>
</html>
