<?php
// Получаем данные из переданного контекста
$user = $c['user'] ?? [];
$all_languages = $c['all_languages'] ?? [];
$selected_lang_ids = $c['selected_lang_ids'] ?? [];
$csrf_token = $c['csrf_token'] ?? '';
$is_admin = $c['is_admin'] ?? false;
$messages = $c['messages'] ?? [];
$errors = $c['errors'] ?? [];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование пользователя #<?= htmlspecialchars($user['id'] ?? '') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .language-checkbox {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        .form-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-title {
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="form-container">
            <h2 class="form-title">Редактирование пользователя #<?= htmlspecialchars($user['id'] ?? '') ?></h2>
            
            <!-- Сообщения об ошибках/успехе -->
            <?php if (!empty($messages)): ?>
                <div class="alert alert-info"><?= htmlspecialchars(implode('<br>', $messages)) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars(implode('<br>', $errors)) ?></div>
            <?php endif; ?>

            <form id="edit-user-form" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="fio" class="form-label">ФИО *</label>
                        <input type="text" class="form-control <?= !empty($errors['fio']) ? 'is-invalid' : '' ?>" 
                               id="fio" name="fio" value="<?= htmlspecialchars($user['fio'] ?? '') ?>" required>
                        <?php if (!empty($errors['fio'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['fio']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>" 
                               id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        <?php if (!empty($errors['email'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="tel" class="form-label">Телефон *</label>
                        <input type="tel" class="form-control <?= !empty($errors['tel']) ? 'is-invalid' : '' ?>" 
                               id="tel" name="tel" value="<?= htmlspecialchars($user['tel'] ?? '') ?>" required>
                        <?php if (!empty($errors['tel'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['tel']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="bdate" class="form-label">Дата рождения *</label>
                        <input type="date" class="form-control <?= !empty($errors['bdate']) ? 'is-invalid' : '' ?>" 
                               id="bdate" name="bdate" value="<?= htmlspecialchars($user['bdate'] ?? '') ?>" required>
                        <?php if (!empty($errors['bdate'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['bdate']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Пол *</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" id="gender-male" 
                                   value="Male" <?= ($user['gender'] ?? '') == 'Male' ? 'checked' : '' ?> required>
                            <label class="form-check-label" for="gender-male">Мужской</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" id="gender-female" 
                                   value="Female" <?= ($user['gender'] ?? '') == 'Female' ? 'checked' : '' ?> required>
                            <label class="form-check-label" for="gender-female">Женский</label>
                        </div>
                        <?php if (!empty($errors['gender'])): ?>
                            <div class="text-danger" style="font-size: 0.875em"><?= htmlspecialchars($errors['gender']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="ccheck" name="ccheck" 
                                   value="1" <?= ($user['ccheck'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ccheck">Согласен с условиями</label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="bio" class="form-label">Биография</label>
                    <textarea class="form-control <?= !empty($errors['bio']) ? 'is-invalid' : '' ?>" 
                              id="bio" name="bio" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    <?php if (!empty($errors['bio'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['bio']) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Языки программирования *</label>
                    <div class="language-checkbox">
                        <?php foreach ($all_languages as $lang): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="languages[]" 
                                       id="lang-<?= $lang['id'] ?>" value="<?= $lang['id'] ?>"
                                       <?= in_array($lang['id'], $selected_lang_ids) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="lang-<?= $lang['id'] ?>">
                                    <?= htmlspecialchars($lang['name']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($errors['languages'])): ?>
                        <div class="text-danger" style="font-size: 0.875em"><?= htmlspecialchars($errors['languages']) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="<?= $is_admin ? 'admin_panel.php' : '/' ?>" class="btn btn-secondary">Назад</a>
                    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('edit-user-form').addEventListener('submit', function(e) {
            // Валидация перед отправкой
            const languages = document.querySelectorAll('input[name="languages[]"]:checked');
            if (languages.length === 0) {
                e.preventDefault();
                alert('Пожалуйста, выберите хотя бы один язык программирования');
                return false;
            }
            return true;
        });
    </script>
</body>
</html>