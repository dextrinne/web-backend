<?php
header('Content-Type: text/html; charset=UTF-8');

// Функция для безопасного получения переменной из $_POST
function getPostParam($paramName, $defaultValue = null) {
    return isset($_POST[$paramName]) ? trim($_POST[$paramName]) : $defaultValue;
}


// Проверка HTTP-авторизации
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Требуется авторизация';
    exit();
}

// Соединение с базой данных
$user = 'u68595';
$pass = '6788124';
try {
    $db = new PDO(
        'mysql:host=localhost;dbname=u68595',
        $user,
        $pass,
        [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); 
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных: ' . $e->getMessage());
}

// Проверка учетных данных администратора
$admin_login = $_SERVER['PHP_AUTH_USER'];
$admin_pass = $_SERVER['PHP_AUTH_PW'];

$stmt = $db->prepare("SELECT password FROM admin WHERE login = ?");
$stmt->execute([$admin_login]);
$admin_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin_data || !password_verify($admin_pass, $admin_data['password'])) { 
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Неверные учетные данные';
    exit();
}

// Функция для получения всех пользователей
function getAllUsers($db) {
    $stmt = $db->prepare("
        SELECT
            u.id, u.fio, u.tel, u.email, u.bdate, u.gender, u.bio, u.ccheck,
            GROUP_CONCAT(l.name SEPARATOR ', ') as languages
        FROM user u
        LEFT JOIN user_language ul ON u.id = ul.user_id
        LEFT JOIN language l ON ul.lang_id = l.id
        GROUP BY u.id
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Функция для получения статистики по языкам
function getLanguageStats($db) {
    $stmt = $db->prepare("
        SELECT l.name, COUNT(ul.user_id) as user_count
        FROM language l
        LEFT JOIN user_language ul ON l.id = ul.lang_id
        GROUP BY l.id
        ORDER BY user_count DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Обработка действий администратора
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_user'])) {
        $stmt = $db->prepare("DELETE FROM user WHERE id = ?");
        $stmt->execute([$_POST['user_id']]);
    } elseif (isset($_POST['update_user'])) {
        $user_id = getPostParam('user_id');
        $fio = getPostParam('fio');
        $tel = getPostParam('tel');
        $email = getPostParam('email');
        $bdate = getPostParam('bdate');
        $gender = getPostParam('gender');
        $bio = getPostParam('bio');
        $ccheck = isset($_POST['ccheck']) ? 1 : 0;

        $stmt = $db->prepare("
            UPDATE user
            SET fio = ?, tel = ?, email = ?, bdate = ?, gender = ?, bio = ?, ccheck = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $fio,
            $tel,
            $email,
            $bdate,
            $gender,
            $bio,
            $ccheck,
            $user_id
        ]);

        // Обновление языков программирования
        $stmt = $db->prepare("DELETE FROM user_language WHERE user_id = ?");
        $stmt->execute([$user_id]);

        if (!empty($_POST['languages']) && is_array($_POST['languages'])) {
            $stmt = $db->prepare("INSERT INTO user_language (user_id, lang_id) VALUES (?, ?)");
            foreach ($_POST['languages'] as $lang_id) {
                $stmt->execute([$user_id, $lang_id]);
            }
        }
    }

    // Перенаправляем, чтобы избежать повторной отправки формы
    header("Location: admin.php");
    exit();
}

// Получение данных для отображения
$users = getAllUsers($db);
$language_stats = getLanguageStats($db);
$all_languages = $db->query("SELECT id, name FROM language")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Административная панель</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1, h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .edit-form {
            display: none;
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            margin-top: 10px;
        }
        button {
            padding: 5px 10px;
            margin: 2px;
            cursor: pointer;
        }
        .delete-btn {
            background-color: #ff6b6b;
            color: white;
            border: none;
        }
        .edit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
        }
        .stats {
            margin-top: 30px;
        }
        .chart {
            display: flex;
            margin-top: 20px;
        }
        .bar {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 5px;
            margin-right: 5px;
        }
    </style>
</head>
<body>
<h1>Административная панель</h1>

<h2>Пользователи</h2>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>ФИО</th>
        <th>Телефон</th>
        <th>Email</th>
        <th>Дата рождения</th>
        <th>Пол</th>
        <th>Биография</th>
        <th>Контракт</th>
        <th>Языки</th>
        <th>Действия</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['fio']) ?></td>
            <td><?= htmlspecialchars($user['tel']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['bdate']) ?></td>
            <td><?= htmlspecialchars($user['gender']) ?></td>
            <td><?= htmlspecialchars($user['bio']) ?></td>
            <td><?= $user['ccheck'] ? 'Да' : 'Нет' ?></td>
            <td><?= htmlspecialchars($user['languages']) ?></td>
            <td>
                <button class="edit-btn" onclick="showEditForm(<?= $user['id'] ?>)">Редактировать</button>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <button type="submit" name="delete_user" class="delete-btn">Удалить</button>
                </form>
            </td>
        </tr>
        <tr id="edit-form-<?= $user['id'] ?>" class="edit-form">
            <td colspan="10">
                <form method="post">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <div>
                        <label>ФИО:</label>
                        <input type="text" name="fio" value="<?= htmlspecialchars($user['fio']) ?>" required>
                    </div>
                    <div>
                        <label>Телефон:</label>
                        <input type="text" name="tel" value="<?= htmlspecialchars($user['tel']) ?>" required>
                    </div>
                    <div>
                        <label>Email:</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div>
                        <label>Дата рождения:</label>
                        <input type="date" name="bdate" value="<?= htmlspecialchars($user['bdate']) ?>" required>
                    </div>
                    <div>
                        <label>Пол:</label>
                        <select name="gender" required>
                            <option value="Male" <?= $user['gender'] == 'Male' ? 'selected' : '' ?>>Мужской</option>
                            <option value="Female" <?= $user['gender'] == 'Female' ? 'selected' : '' ?>>Женский</option>
                        </select>
                    </div>
                    <div>
                        <label>Биография:</label>
                        <textarea name="bio"><?= htmlspecialchars($user['bio']) ?></textarea>
                    </div>
                    <div>
                        <label>
                            <input type="checkbox" name="ccheck" <?= $user['ccheck'] ? 'checked' : '' ?>>
                            Ознакомлен с контрактом
                        </label>
                    </div>
                    <div>
                        <label>Языки программирования:</label><br>
                        <?php foreach ($all_languages as $lang): ?>
                            <label>
                                <input type="checkbox" name="languages[]" value="<?= $lang['id'] ?>"
                                    <?= strpos($user['languages'], $lang['name']) !== false ? 'checked' : '' ?>>
                                <?= htmlspecialchars($lang['name']) ?>
                            </label><br>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" name="update_user">Сохранить</button>
                    <button type="button" onclick="hideEditForm(<?= $user['id'] ?>)">Отмена</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="stats">
    <h2>Статистика по языкам программирования</h2>
    <table>
        <thead>
        <tr>
            <th>Язык</th>
            <th>Количество пользователей</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($language_stats as $stat): ?>
            <tr>
                <td><?= htmlspecialchars($stat['name']) ?></td>
                <td><?= $stat['user_count'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="chart">
        <?php foreach ($language_stats as $stat): ?>
            <div class="bar" style="height: <?= $stat['user_count'] * 20 ?>px; width: 50px;"
                 title="<?= htmlspecialchars($stat['name']) ?>: <?= $stat['user_count'] ?>">
                <?= $stat['user_count'] ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function showEditForm(userId) {
        // Скрываем все формы редактирования
        document.querySelectorAll('.edit-form').forEach(form => {
            form.style.display = 'none';
        });
        // Показываем нужную форму
        document.getElementById('edit-form-' + userId).style.display = 'table-row';
    }

    function hideEditForm(userId) {
        document.getElementById('edit-form-' + userId).style.display = 'none';
    }
</script>
</body>
</html>
