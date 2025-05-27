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
            background-color: #778DA9;
            color: #0D1B2A;
        }

        tbody {
            background-color: #E0E1DD;
        }

        h1, h2 {
            color: #0D1B2A;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #778DA9;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
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
            text-decoration: none;
            display: inline-block;
            padding: 2.6pt 2pt;
            background-color: #4CAF50;
            color: white;
            border: none;
        }

        .stats {
            margin-top: 30px;
        }

        .admin-error {
            color: red;
            padding: 10px;
            background: #ffebeb;
            border: 1px solid red;
            margin-bottom: 20px;
        }

        .admin-success {
            color: green;
            padding: 10px;
            background: #ebffeb;
            border: 1px solid green;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <?php if (!empty($_SESSION['admin_error'])): ?>
        <div class="admin-error">
            <?= htmlspecialchars($_SESSION['admin_error']) ?>
        </div>
        <?php unset($_SESSION['admin_error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['admin_success'])): ?>
        <div class="admin-success">
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
                        <a href="/web-backend/web/edit_user/<?= $user['id'] ?>" class="edit-btn">Редактировать</a>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" name="delete_user" class="delete-btn"
                                onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')">Удалить</button>
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
</body>
</html>