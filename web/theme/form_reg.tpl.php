<?php
$is_edit_mode = $c['is_edit_mode'] ?? false;
$errors = $c['errors'] ?? [];
$values = $c['values'] ?? [];
$messages = $c['messages'] ?? [];
$is_auth = $c['is_auth'] ?? false;
$is_admin = $c['is_admin'] ?? false;
$csrf_token = $c['csrf_token'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $is_edit_mode ? 'Редактирование данных' : 'Форма регистрации' ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
        .error-input {
            border: 1px solid red !important;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        .required {
            color: red;
        }
        #languages {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        textarea {
            width: 100%;
            min-height: 100px;
            padding: 8px;
        }
        input[type="text"],
        input[type="email"],
        input[type="tel"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        button[type="submit"] {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button[type="submit"]:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

    </style>
</head>
<body style="background-color: #f8f9fa;">
    <div class="container">
        <div class="form-container">
            <h2 class="form-title"><?= $is_edit_mode ? 'Редактирование данных' : 'Форма регистрации' ?></h2>
            
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
            
            <form method="post" id="user-form" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <!-- ФИО -->
                <div class="mb-3">
                    <label for="fio" class="form-label">ФИО <span class="required-star">*</span></label>
                    <input type="text" class="form-control <?= !empty($errors['fio']) ? 'error-input' : '' ?>" 
                           id="fio" name="fio" required
                           value="<?= htmlspecialchars($values['fio'] ?? '') ?>">
                    <?php if (!empty($errors['fio'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['fio']) ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Телефон -->
                <div class="mb-3">
                    <label for="phone" class="form-label">Телефон <span class="required-star">*</span></label>
                    <input type="tel" class="form-control <?= !empty($errors['phone']) ? 'error-input' : '' ?>" 
                           id="phone" name="phone" required
                           value="<?= htmlspecialchars($values['phone'] ?? '') ?>">
                    <?php if (!empty($errors['phone'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['phone']) ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="required-star">*</span></label>
                    <input type="email" class="form-control <?= !empty($errors['email']) ? 'error-input' : '' ?>" 
                           id="email" name="email" required
                           value="<?= htmlspecialchars($values['email'] ?? '') ?>">
                    <?php if (!empty($errors['email'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['email']) ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Дата рождения -->
                <div class="mb-3">
                    <label for="birthdate" class="form-label">Дата рождения <span class="required-star">*</span></label>
                    <input type="date" class="form-control <?= !empty($errors['birthdate']) ? 'error-input' : '' ?>" 
                           id="birthdate" name="birthdate" required
                           value="<?= htmlspecialchars($values['birthdate'] ?? '') ?>">
                    <?php if (!empty($errors['birthdate'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['birthdate']) ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Пол -->
                <div class="mb-3">
                    <label class="form-label">Пол <span class="required-star">*</span></label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="gender-male" value="male" required
                                <?= (isset($values['gender']) && $values['gender'] === 'male') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="gender-male">Мужской</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="gender-female" value="female" required
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
                    <label class="form-label">Языки программирования <span class="required-star">*</span></label>
                    <div class="d-flex flex-wrap">
                        <?php foreach ([
                            1 => 'Pascal', 2 => 'C', 3 => 'C++', 4 => 'JavaScript',
                            5 => 'PHP', 6 => 'Python', 7 => 'Java', 8 => 'Haskell',
                            9 => 'Clojure', 10 => 'Prolog', 11 => 'Scala', 12 => 'Go'
                        ] as $id => $name): ?>
                            <div class="language-checkbox form-check">
                                <input class="form-check-input" type="checkbox" name="languages[]" 
                                       id="lang-<?= $id ?>" value="<?= $id ?>"
                                       <?= in_array($id, $values['languages'] ?? []) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="lang-<?= $id ?>">
                                    <?= htmlspecialchars($name) ?>
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
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input <?= !empty($errors['agreement']) ? 'error-input' : '' ?>" 
                               type="checkbox" id="agreement" name="agreement" value="1" required
                               <?= !empty($values['agreement']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="agreement">
                            Я согласен(а) с условиями контракта <span class="required-star">*</span>
                        </label>
                    </div>
                    <?php if (!empty($errors['agreement'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['agreement']) ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Кнопки отправки/назад -->
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">
                        <?= $is_edit_mode ? 'Обновить данные' : 'Зарегистрироваться' ?>
                    </button>
                    <?php if ($is_edit_mode && $is_admin): ?>
                        <a href="admin_panel.php" class="btn btn-secondary">Назад в админку</a>
                    <?php elseif ($is_edit_mode): ?>
                        <a href="/" class="btn btn-secondary">На главную</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Валидация формы на клиенте
        document.getElementById('user-form').addEventListener('submit', function(e) {
            // Проверка обязательных полей
            const requiredFields = ['fio', 'phone', 'email', 'birthdate', 'gender', 'agreement'];
            let isValid = true;
            
            requiredFields.forEach(field => {
                const element = field === 'gender' ? 
                    document.querySelector(`input[name="${field}"]:checked`) :
                    document.getElementById(field);
                
                if (!element || (element.type === 'checkbox' && !element.checked)) {
                    isValid = false;
                    const errorElement = document.querySelector(`#${field}-error`) || 
                                        document.querySelector(`.error[for="${field}"]`);
                    if (errorElement) {
                        errorElement.textContent = 'Это поле обязательно для заполнения';
                    }
                }
            });
            
            // Проверка языков программирования
            const langCheckboxes = document.querySelectorAll('input[name="languages[]"]:checked');
            if (langCheckboxes.length === 0) {
                isValid = false;
                const langError = document.querySelector('#languages-error');
                if (langError) {
                    langError.textContent = 'Выберите хотя бы один язык';
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Пожалуйста, заполните все обязательные поля корректно!');
            }
        });

        // Маска для телефона
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('7')) {
                value = '+7' + value.substring(1);
            } else if (value.startsWith('8')) {
                value = '+7' + value.substring(1);
            } else if (value) {
                value = '+7' + value;
            }
            e.target.value = value;
        });
    </script>
</body>
</html>