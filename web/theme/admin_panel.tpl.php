<div class="admin-container">
    <h1>Административная панель</h1>

    <?php if (!empty($c['admin_message'])): ?>
        <div class="alert alert-info"><?php echo $c['admin_message']; ?></div>
    <?php endif; ?>

    <h2>Данные пользователей</h2>
    <?php if (!empty($c['applications'])): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ФИО</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Дата рождения</th>
                    <th>Пол</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($c['applications'] as $app): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($app['id']); ?></td>
                        <td><?php echo htmlspecialchars($app['fio']); ?></td>
                        <td><?php echo htmlspecialchars($app['phone']); ?></td>
                        <td><?php echo htmlspecialchars($app['email']); ?></td>
                        <td><?php echo htmlspecialchars($app['birthdate']); ?></td>
                        <td><?php echo htmlspecialchars($app['gender']); ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $app['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $c['csrf_token']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                            </form>
                            <button class="btn btn-primary btn-sm" onclick="showEditForm(<?php echo $app['id']; ?>)">
                                Редактировать
                            </button>
                        </td>
                    </tr>
                   <tr id="editForm_<?php echo $app['id']; ?>" style="display:none;">
                        <td colspan="7">
                            <form method="post">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?php echo $app['id']; ?>">
                                 <input type="hidden" name="csrf_token" value="<?php echo $c['csrf_token']; ?>">

                                <label for="fio">ФИО:</label>
                                <input type="text" name="fio" value="<?php echo htmlspecialchars($app['fio']); ?>"><br>

                                <label for="phone">Телефон:</label>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($app['phone']); ?>"><br>

                                <label for="email">Email:</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($app['email']); ?>"><br>

                                 <label for="birthdate">Дата рождения:</label>
                                <input type="date" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($app['birthdate'] ?? ''); ?>">
                                <label>Пол:</label>
                                <select name="gender">
                                    <option value="male" <?php echo ($app['gender'] == 'male') ? 'selected' : ''; ?>>Мужской</option>
                                    <option value="female" <?php echo ($app['gender'] == 'female') ? 'selected' : ''; ?>>Женский</option>
                                </select><br>

                                 <label for="bio">Биография:</label>
                                <textarea name="bio"><?php echo htmlspecialchars($app['bio']); ?></textarea><br>

                                <label>Языки:</label>
                                <select name="languages[]" multiple>
                                    <option value="1" <?php if (in_array(1, explode(',', $app['languages']))) echo 'selected'; ?>>Pascal</option>
                                    <option value="2" <?php if (in_array(2, explode(',', $app['languages']))) echo 'selected'; ?>>C</option>
                                    <!-- Добавьте остальные языки -->
                                </select><br>
                                <label for="agreement">Согласие:</label>
                                <input type="checkbox" name="agreement" <?php echo ($app['agreement']) ? 'checked' : ''; ?>><br>

                                <button type="submit">Сохранить изменения</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Нет данных для отображения</p>
    <?php endif; ?>

    <h2>Статистика по языкам</h2>
    <?php if (!empty($c['language_stats'])): ?>
        <ul>
            <?php foreach ($c['language_stats'] as $stat): ?>
                <li><?php echo htmlspecialchars($stat['name']); ?>: <?php echo $stat['count']; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Нет статистики</p>
    <?php endif; ?>

    <p><a href="logout.php" class="btn btn-secondary">Выйти</a></p>
</div>

<script>
function showEditForm(id) {
    var form = document.getElementById('editForm_' + id);
    if (form) {
        form.style.display = form.style.display === 'table-row' ? 'none' : 'table-row';
    }
}
</script>
