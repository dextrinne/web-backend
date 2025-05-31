<!DOCTYPE html>
<html>
<head>
    <title><?php echo $c['is_auth'] ? 'Редактирование данных' : 'Форма регистрации'; ?></title>
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
        input[type="tel"],
        input[type="date"] {
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
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-actions {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="form-container" id="form-anchor">
        <div id="form-messages"></div>
        
        <div class="form-actions">
            <?php if ($c['is_auth']): ?>
                <a href="logout.php" class="btn btn-danger">Выйти</a>
            <?php else: ?>
                <?php if (!$c['show_login']): ?>
                    <a href="?show_login=true#form-anchor" id="toggle-auth-btn" class="btn btn-secondary">Войти</a>
                <?php else: ?>
                    <a href="?#form-anchor" id="toggle-auth-btn" class="btn btn-secondary">Регистрация</a>
                <?php endif; ?>
                <a href="./modules/admin_panel.php" class="btn btn-admin" target="_blank"
                style="background-color: #dc3545; color: white; border: none;">Администратор</a>
            <?php endif; ?>
        </div>
        
        <?php if ($c['show_login'] && !$c['is_auth']): ?>
            <!-- Форма входа -->
            <form method="post" id="login-form" action="login.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($c['csrf_token']); ?>">
                <div class="form-group">
                    <label for="login">Логин:</label>
                    <input type="text" id="login" name="login" required>
                </div>
                <div class="form-group">
                    <label for="password">Пароль:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Войти</button>
            </form>
        <?php else: ?>
            <!-- Общая форма (регистрация/редактирование) -->
            <form method="post" id="registration-form" class="contact-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($c['csrf_token']); ?>">
                
                <!-- ФИО -->
                <div class="form-group">
                    <label for="fio">ФИО: <span class="required">*</span></label>
                    <input type="text" id="fio" name="fio" required
                        value="<?php echo htmlspecialchars($c['values']['fio'] ?? ''); ?>"
                        class="<?php echo !empty($c['errors']['fio']) ? 'error-input' : ''; ?>">
                    <div id="fio-error" class="error">
                        <?php echo !empty($c['errors']['fio']) ? htmlspecialchars($c['errors']['fio']) : ''; ?>
                    </div>
                </div>
                
                <!-- Телефон -->
                <div class="form-group">
                    <label for="tel">Телефон: <span class="required">*</span></label>
                    <input type="tel" id="tel" name="tel" placeholder="+7 (XXX) XXX-XX-XX" required
                        value="<?php echo htmlspecialchars($c['values']['tel'] ?? ''); ?>"
                        class="<?php echo !empty($c['errors']['tel']) ? 'error-input' : ''; ?>">
                    <div id="tel-error" class="error">
                        <?php echo !empty($c['errors']['tel']) ? htmlspecialchars($c['errors']['tel']) : ''; ?>
                    </div>
                </div>
                
                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email: <span class="required">*</span></label>
                    <input type="email" id="email" name="email" placeholder="example@domain.com" required
                        value="<?php echo htmlspecialchars($c['values']['email'] ?? ''); ?>"
                        class="<?php echo !empty($c['errors']['email']) ? 'error-input' : ''; ?>">
                    <div id="email-error" class="error">
                        <?php echo !empty($c['errors']['email']) ? htmlspecialchars($c['errors']['email']) : ''; ?>
                    </div>
                </div>
                
                <!-- Дата рождения -->
                <div class="form-group">
                    <label for="bdate">Дата рождения: <span class="required">*</span></label>
                    <input type="date" id="bdate" name="bdate" required
                        value="<?php echo htmlspecialchars($c['values']['bdate'] ?? ''); ?>"
                        class="<?php echo !empty($c['errors']['bdate']) ? 'error-input' : ''; ?>">
                    <div id="bdate-error" class="error">
                        <?php echo !empty($c['errors']['bdate']) ? htmlspecialchars($c['errors']['bdate']) : ''; ?>
                    </div>
                </div>
                
                <!-- Пол -->
                <div class="form-group">
                    <label>Пол: <span class="required">*</span></label>
                    <div class="radio-group">
                        <label><input type="radio" name="gender" value="Male" required <?php echo (isset($c['values']['gender']) && $c['values']['gender'] == 'Male') ? 'checked' : ''; ?>>
                            <span>Мужской</span></label>
                        <label><input type="radio" name="gender" value="Female" required <?php echo (isset($c['values']['gender']) && $c['values']['gender'] == 'Female') ? 'checked' : ''; ?>>
                            <span>Женский</span></label>
                    </div>
                    <div id="gender-error" class="error">
                        <?php echo !empty($c['errors']['gender']) ? htmlspecialchars($c['errors']['gender']) : ''; ?>
                    </div>
                </div>
                
                <!-- Языки программирования -->
                <div class="form-group">
                    <label for="languages">Любимые языки программирования: <span class="required">*</span></label>
                    <select id="languages" name="languages[]" multiple required
                        class="<?php echo !empty($c['errors']['languages']) ? 'error-input' : ''; ?>">
                        <?php foreach ($c['all_languages'] as $lang): ?>
                            <option value="<?php echo $lang['id']; ?>" <?php echo in_array($lang['id'], $c['selected_lang_ids'] ?? []) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lang['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="languages-error" class="error">
                        <?php echo !empty($c['errors']['languages']) ? htmlspecialchars($c['errors']['languages']) : ''; ?>
                    </div>
                    <small>Для выбора нескольких языков удерживайте Ctrl (Windows) или Command (Mac)</small>
                </div>
                
                <!-- Биография -->
                <div class="form-group">
                    <label for="bio">Биография:</label>
                    <textarea id="bio" name="bio"
                        class="<?php echo !empty($c['errors']['bio']) ? 'error-input' : ''; ?>"><?php echo htmlspecialchars($c['values']['bio'] ?? ''); ?></textarea>
                    <div id="bio-error" class="error">
                        <?php echo !empty($c['errors']['bio']) ? htmlspecialchars($c['errors']['bio']) : ''; ?>
                    </div>
                </div>
                
                <!-- Соглашение -->
                <div class="form-group">
                    <label class="form-check-label1" style="display: block;">
                        <input type="checkbox" id="ccheck" name="ccheck" value="1" required class="form-check-input" <?php echo (!empty($c['values']['ccheck'])) ? 'checked' : ''; ?>>
                        Я согласен(а) с условиями контракта <span class="required">*</span>
                    </label>
                    <div id="ccheck-error" class="error">
                        <?php echo !empty($c['errors']['ccheck']) ? htmlspecialchars($c['errors']['ccheck']) : ''; ?>
                    </div>
                </div>
                
                <button type="submit" id="submit-btn"><?php echo $c['is_auth'] ? 'Обновить данные' : 'Зарегистрироваться'; ?></button>
        </form>
        <?php endif; ?>
    </div>

    <script>
        // Обработка переключения между формами
        document.getElementById('toggle-auth-btn')?.addEventListener('click', function() {
            window.location.href = '?show_login=' + (!<?php echo $c['show_login'] ? 'true' : 'false' ?>);
        });

        // Обработка отправки формы
        document.getElementById('registration-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        // Блокируем кнопку
        submitBtn.disabled = true;
        submitBtn.textContent = 'Отправка...';
        
        try {
            const formData = new FormData(form);
            
            // Добавляем языки программирования
            const languages = document.getElementById('languages');
            formData.delete('languages[]');
            Array.from(languages.selectedOptions).forEach(option => {
                formData.append('languages[]', option.value);
            });
            
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Успешная регистрация
                const messageDiv = document.createElement('div');
                messageDiv.className = 'alert alert-success';
                messageDiv.innerHTML = `
                    <h4>Успешная регистрация!</h4>
                    <p><strong>Логин:</strong> ${result.login}</p>
                    <p><strong>Пароль:</strong> ${result.password}</p>
                    <p>Сохраните эти данные</p>
                `;
                document.getElementById('form-messages').appendChild(messageDiv);
                
                // Очищаем форму
                form.reset();
            } else {
                // Ошибки валидации
                if (result.errors) {
                    for (const [field, error] of Object.entries(result.errors)) {
                        const errorElement = document.getElementById(`${field}-error`);
                        if (errorElement) {
                            errorElement.textContent = error;
                            const input = form.querySelector(`[name="${field}"]`);
                            if (input) input.classList.add('error-input');
                        }
                    }
                }
            }
        } catch (error) {
            console.error('Ошибка:', error);
            const messageDiv = document.createElement('div');
            messageDiv.className = 'alert alert-danger';
            messageDiv.textContent = 'Произошла ошибка при отправке формы';
            document.getElementById('form-messages').appendChild(messageDiv);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
    </script>
</body>
</html>