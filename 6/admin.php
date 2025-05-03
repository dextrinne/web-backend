<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

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
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных: ' . $e->getMessage());
}

// Проверка учетных данных администратора
$admin_login = $_SERVER['PHP_AUTH_USER'];
$admin_pass = $_SERVER['PHP_AUTH_PW'];

try {
    // Получаем хеш пароля из базы данных
    $stmt = $db->prepare("SELECT password FROM admin WHERE login = ?");
    $stmt->execute([$admin_login]);
    $admin_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin_data) {
        // Администратор с таким логином не найден
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Неверные учетные данные';
        exit();
    }

    // Сравниваем хеши паролей
    $hashed_input = hash('sha256', $admin_pass);
    if ($hashed_input !== $admin_data['password']) {
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Неверные учетные данные';
        exit();
    }
} catch (PDOException $e) {
    die('Ошибка проверки учетных данных: ' . $e->getMessage());
}

// Генерация CSRF-токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Функция для получения всех пользователей
function getAllUsers($db) {
    try {
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
    } catch (PDOException $e) {
        die('Ошибка получения пользователей: ' . $e->getMessage());
    }
}

// Функция для получения статистики по языкам
function getLanguageStats($db) {
    try {
        $stmt = $db->prepare("
            SELECT l.name, COUNT(ul.user_id) as user_count
            FROM language l
            LEFT JOIN user_language ul ON l.id = ul.lang_id
            GROUP BY l.id
            ORDER BY user_count DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Ошибка получения статистики: ' . $e->getMessage());
    }
}

// Обработка действий администратора
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Проверка CSRF-токена
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Неверный CSRF-токен');
    }

    try {
        if (isset($_POST['delete_user'])) {
            // Удаление пользователя
            $stmt = $db->prepare("DELETE FROM user WHERE id = ?");
            $stmt->execute([intval($_POST['user_id'])]);
            
        } elseif (isset($_POST['update_user'])) {
            // Валидация данных
            $fio = trim($_POST['fio']);
            $tel = trim($_POST['tel']);
            $email = trim($_POST['email']);
            $bdate = $_POST['bdate'];
            $gender = in_array($_POST['gender'], ['Male', 'Female']) ? $_POST['gender'] : 'Male';
            $bio = trim($_POST['bio']);
            $ccheck = isset($_POST['ccheck']) ? 1 : 0;
            $user_id = intval($_POST['user_id']);
            
            if (empty($fio) || empty($tel) || empty($email) || empty($bdate)) {
                die('Не все обязательные поля заполнены');
            }
            
            // Обновление данных пользователя
            $stmt = $db->prepare("
                UPDATE user 
                SET fio = ?, tel = ?, email = ?, bdate = ?, gender = ?, bio = ?, ccheck = ?
                WHERE id = ?
            ");
            $stmt->execute([$fio, $tel, $email, $bdate, $gender, $bio, $ccheck, $user_id]);
            
            // Обновление языков программирования
            $stmt = $db->prepare("DELETE FROM user_language WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            if (!empty($_POST['languages']) && is_array($_POST['languages'])) {
                $stmt = $db->prepare("INSERT INTO user_language (user_id, lang_id) VALUES (?, ?)");
                foreach ($_POST['languages'] as $lang_id) {
                    $stmt->execute([$user_id, intval($lang_id)]);
                }
            }
        }
    } catch (PDOException $e) {
        die('Ошибка выполнения операции: ' . $e->getMessage());
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
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #cbeaed;
        }
        tbody {
            background-color: #f5f5f5;
        }
        h1, h2 {
            color: #006a71;
            text-align: center;
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
        label {
            display: inline-block;
            min-width: 150px;
            margin: 5px 0;
        }
        input[type="text"], 
        input[type="email"], 
        input[type="date"], 
        textarea, 
        select {
            width: 300px;
            padding: 5px;
            margin: 5px 0;
        }
        textarea {
            height: 100px;
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
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" name="delete_user" class="delete-btn" 
                                onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')">Удалить</button>
                    </form>
                </td>
            </tr>
            <tr id="edit-form-<?= $user['id'] ?>" class="edit-form">
                <td colspan="10">
                    <form method="post">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
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
                            <label style="min-width: auto; display: inline-block; width: 120px;">
                                <input type="checkbox" name="languages[]" value="<?= $lang['id'] ?>"
                                    <?= strpos($user['languages'], $lang['name']) !== false ? 'checked' : '' ?>>
                                <?= htmlspecialchars($lang['name']) ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <div style="margin-top: 10px;">
                            <button type="submit" name="update_user">Сохранить</button>
                            <button type="button" onclick="hideEditForm(<?= $user['id'] ?>)">Отмена</button>
                        </div>
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
    </div>
    
    <script>
        function showEditForm(userId) {
            // Скрываем все формы редактирования
            document.querySelectorAll('.edit-form').forEach(form => {
                form.style.display = 'none';
            });
            // Показываем нужную форму
            document.getElementById('edit-form-' + userId).style.display = 'table-row';
            // Прокручиваем к форме
            document.getElementById('edit-form-' + userId).scrollIntoView({behavior: 'smooth'});
        }
        
        function hideEditForm(userId) {
            document.getElementById('edit-form-' + userId).style.display = 'none';
        }
    </script>
</body>
</html>
