<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'includes/security_headers.php';
session_start();
include('./actions/db.php');
include('./actions/functions.php');

// Проверка HTTP-авторизации
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Требуется авторизация';
    exit();
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
    error_log('Admin authentication error: ' . $e->getMessage());
    die('Ошибка проверки учетных данных.');
}


// Генерация CSRF-токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Обработка действий администратора
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Проверка CSRF-токена
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['admin_error'] = htmlspecialchars('Неверный CSRF-токен');
        header("Location: admin.php");
        exit();
    }

    try {
        if (isset($_POST['delete_user'])) {
            // Удаление пользователя
            $stmt = $db->prepare("DELETE FROM user WHERE id = ?");
            $stmt->execute([intval($_POST['user_id'])]);

        } elseif (isset($_POST['update_user'])) {
            include('./actions/validation.php');

            $_POST['radio'] = $_POST['gender'];
            $_POST['abilities'] = $_POST['languages'] ?? [];

            $all_languages = $db->query("SELECT id, name FROM language")->fetchAll(PDO::FETCH_ASSOC);
            $abilities = [];
            foreach ($all_languages as $lang) {
                $abilities[$lang['id']] = $lang['name'];
            }

            $dummy_errors = false;
            $dummy_values = array();
            if (!validateFormData($db, $dummy_errors, $dummy_values, $abilities)) {
                $_SESSION['admin_error'] = htmlspecialchars('Ошибка валидации данных. Проверьте введенные значения.');
                header("Location: admin.php");
                exit();
            }

            $user_id = intval($_POST['user_id']);

            $stmt = $db->prepare("
                UPDATE user 
                SET fio = ?, tel = ?, email = ?, bdate = ?, gender = ?, bio = ?, ccheck = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['fio'],
                $_POST['tel'],
                $_POST['email'],
                $_POST['bdate'],
                $_POST['gender'],
                $_POST['bio'],
                isset($_POST["ccheck"]) ? 1 : 0,
                $user_id
            ]);

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
        error_log('Admin operation error: ' . $e->getMessage());
        $_SESSION['admin_error'] = 'Произошла ошибка при выполнении операции. Пожалуйста, попробуйте позже.';
        header("Location: admin.php");
        exit();
    }


    $_SESSION['admin_success'] = 'Изменения успешно сохранены';
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

        h1,
        h2 {
            color: #006a71;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
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
    <?php if (!empty($_SESSION['admin_error'])): ?>
        <div class="admin-error"
            style="color: red; padding: 10px; background: #ffebeb; border: 1px solid red; margin-bottom: 20px;">
            <?= htmlspecialchars($_SESSION['admin_error']) ?>
        </div>
        <?php unset($_SESSION['admin_error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['admin_success'])): ?>
        <div class="admin-success"
            style="color: green; padding: 10px; background: #ebffeb; border: 1px solid green; margin-bottom: 20px;">
            <?= htmlspecialchars($_SESSION['admin_success']) ?>
        </div>
        <?php unset($_SESSION['admin_success']); ?>
    <?php endif; ?>

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
                                    <option value="Female" <?= $user['gender'] == 'Female' ? 'selected' : '' ?>>Женский
                                    </option>
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
            document.getElementById('edit-form-' + userId).scrollIntoView({ behavior: 'smooth' });
        }

        function hideEditForm(userId) {
            document.getElementById('edit-form-' + userId).style.display = 'none';
        }
    </script>
</body>

</html>
