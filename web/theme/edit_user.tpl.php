<?php
$errors = $c['errors'] ?? [];
$values = $c['values'] ?? [];
$messages = $c['messages'] ?? [];
$is_admin = $_SESSION['is_admin'] ?? false;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Редактирование данных</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error { color: red; font-size: 0.9em; }
        .error-input { border-color: red; }
        .form-container { max-width: 800px; margin: 30px auto; }
        .language-item { margin-right: 10px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="container form-container">
        <h2>Редактирование данных пользователя</h2>
        
        <?php if (!empty($messages)): ?>
            <div class="alert alert-success">
                <?php foreach ($messages as $message): ?>
                    <p><?= htmlspecialchars($message) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($errors['general']) ?>
            </div>
        <?php endif; ?>
        
        <form method="post" id="edit-form">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <!-- ФИО -->
            <div class="mb-3">
                <label for="fio" class="form-label">ФИО *</label>
                <input type="text" class="form-control <?= !empty($errors['fio']) ? 'error-input' : '' ?>" 
                       id="fio" name="fio" value="<?= htmlspecialchars($values['fio'] ?? '') ?>">
                <?php if (!empty($errors['fio'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['fio']) ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Телефон -->
            <div class="mb-3">
                <label for="phone" class="form-label">Телефон *</label>
                <input type="tel" class="form-control <?= !empty($errors['phone']) ? 'error-input' : '' ?>" 
                       id="phone" name="phone" value="<?= htmlspecialchars($values['phone'] ?? '') ?>">
                <?php if (!empty($errors['phone'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['phone']) ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control <?= !empty($errors['email']) ? 'error-input' : '' ?>" 
                       id="email" name="email" value="<?= htmlspecialchars($values['email'] ?? '') ?>">
                <?php if (!empty($errors['email'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['email']) ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Дата рождения -->
            <div class="mb-3">
                <label for="birthdate" class="form-label">Дата рождения *</label>
                <input type="date" class="form-control <?= !empty($errors['birthdate']) ? 'error-input' : '' ?>" 
                       id="birthdate" name="birthdate" value="<?= htmlspecialchars($values['birthdate'] ?? '') ?>">
                <?php if (!empty($errors['birthdate'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['birthdate']) ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Пол -->
            <div class="mb-3">
                <label class="form-label">Пол *</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" id="gender-male" value="male"
                            <?= (isset($values['gender']) && $values['gender'] === 'male') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="gender-male">Мужской</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" id="gender-female" value="female"
                            <?= (isset($values['gender']) && $values['gender'] === 'female') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="gender-female">Женский</label>
                    </div>
                </div>
                <?php if (!empty($errors['gender'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['gender']) ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Языки программирования -->
            <div class="mb-3">
                <label class="form-label">Языки программирования *</label>
                <div class="d-flex flex-wrap">
                    <?php foreach ($languages as $lang): ?>
                        <div class="language-item form-check">
                            <input class="form-check-input" type="checkbox" name="languages[]" 
                                   id="lang-<?= $lang['id'] ?>" value="<?= $lang['id'] ?>"
                                   <?= in_array($lang['id'], $values['languages'] ?? []) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="lang-<?= $lang['id'] ?>">
                                <?= htmlspecialchars($lang['name']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($errors['languages'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['languages']) ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Биография -->
            <div class="mb-3">
                <label for="bio" class="form-label">Биография</label>
                <textarea class="form-control <?= !empty($errors['bio']) ? 'error-input' : '' ?>" 
                          id="bio" name="bio" rows="4"><?= htmlspecialchars($values['bio'] ?? '') ?></textarea>
                <?php if (!empty($errors['bio'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['bio']) ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Соглашение -->
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input <?= !empty($errors['agreement']) ? 'error-input' : '' ?>" 
                       id="agreement" name="agreement" value="1"
                       <?= !empty($values['agreement']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="agreement">Я согласен с условиями *</label>
                <?php if (!empty($errors['agreement'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['agreement']) ?></div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
            <?php if ($is_admin): ?>
                <a href="admin_panel.php" class="btn btn-secondary">Вернуться в админку</a>
            <?php endif; ?>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Валидация формы на клиенте
        document.getElementById('edit-form').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Проверка ФИО
            const fio = document.getElementById('fio').value.trim();
            if (!fio) {
                isValid = false;
            }
            
            // Проверка телефона
            const phone = document.getElementById('phone').value.trim();
            if (!phone || !/^\+7\s?\(?\d{3}\)?\s?\d{3}-?\d{2}-?\d{2}$/.test(phone)) {
                isValid = false;
            }
            
            // Проверка email
            const email = document.getElementById('email').value.trim();
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                isValid = false;
            }
            
            // Проверка даты рождения
            const birthdate = document.getElementById('birthdate').value;
            if (!birthdate) {
                isValid = false;
            }
            
            // Проверка пола
            if (!document.querySelector('input[name="gender"]:checked')) {
                isValid = false;
            }
            
            // Проверка языков
            if (!document.querySelector('input[name="languages[]"]:checked')) {
                isValid = false;
            }
            
            // Проверка соглашения
            if (!document.getElementById('agreement').checked) {
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Пожалуйста, заполните все обязательные поля корректно!');
            }
        });
    </script>
</body>
</html>