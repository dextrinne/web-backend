<?php
$login = $c['login'] ?? '';
$password = $c['password'] ?? '';
?>
<div class="container">
    <h1>Форма успешно отправлена!</h1>
    
    <?php if ($login && $password): ?>
        <div class="alert alert-success">
            <h4>Ваши учетные данные:</h4>
            <p><strong>Логин:</strong> <?php echo htmlspecialchars($login); ?></p>
            <p><strong>Пароль:</strong> <?php echo htmlspecialchars($password); ?></p>
            <p class="text-danger">Запишите эти данные, они больше не будут показаны!</p>
        </div>
    <?php else: ?>
        <div class="alert alert-success">
            Ваши данные успешно сохранены.
        </div>
    <?php endif; ?>
    
    <p>
        <a href="/" class="btn btn-primary">Вернуться на главную</a>
        <?php if ($login && $password): ?>
            <a href="login.php" class="btn btn-success">Войти в систему</a>
        <?php endif; ?>
    </p>
</div>