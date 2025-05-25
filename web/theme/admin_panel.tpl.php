<?php
$users = $c['users'] ?? [];
$language_stats = $c['language_stats'] ?? [];
$csrf_token = $c['csrf_token'] ?? '';
$admin_message = $_SESSION['admin_message'] ?? '';

// Очищаем сообщение из сессии после отображения
unset($_SESSION['admin_message']);
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
            margin: 20px;
            background-color: #f5f5f5;
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
        .action-btn {
            padding: 5px 10px;
            margin: 2px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block; /* Чтобы padding работал */
        }
        .edit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px; /* Для красоты */
        }
        .delete-btn {
            background-color: #f44336;
            color: white;
            border: none;
             border-radius: 4px; /* Для красоты */
        }
        .admin-message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #a94442;
        }
    </style>
</head>
<body>
    <h1>Административная панель</h1>

    <?php if (!empty($admin_message)): ?>
        <div class="admin-message <?= strpos($admin_message, 'успешно') !== false ? 'success' : 'error' ?>">
            <?= $admin_message ?>
        </div>
    <?php endif; ?>

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
                    <td><?= htmlspecialchars($user['languages'] ?? 'Нет') ?></td>
                    <td>
                        <a href="<?= conf('basedir') ?>edit_user/<?= $user['id'] ?>" class="action-btn edit-btn">Редактировать</a>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <button type="submit" class="action-btn delete-btn"
                                onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Статистика по языкам</h2>
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
                    <td><?= htmlspecialchars($stat['user_count']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
