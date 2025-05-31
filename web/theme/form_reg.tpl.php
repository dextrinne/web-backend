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
    <div class="form-container">
        <div id="form-messages"></div>
        
        <div class="form-actions">
            <?php if ($c['is_auth']): ?>
                <a href="logout.php" class="btn btn-danger">Выйти</a>
            <?php endif; ?>
            <?php if (!$c['is_auth']): ?>
                <a href="login.php" class="btn btn-secondary" target="_blank">Войти</a>
                <a href="./modules/admin_panel.php" class="btn btn-admin" target="_blank" 
                   style="background-color: #dc3545; color: white; border: none;">Администратор</a>
            <?php endif; ?>
        </div>
        
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
    </div>

    <script>
        document.getElementById('registration-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        // Очистка предыдущих ошибок
        document.querySelectorAll('.error').forEach(el => el.textContent = '');
        document.querySelectorAll('.error-input').forEach(el => el.classList.remove('error-input'));
        
        // Клиентская валидация
        const clientErrors = validateFormClient(form);
        if (Object.keys(clientErrors).length > 0) {
            showErrors(clientErrors);
            return;
        }
        
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
                showSuccessMessage(result);
                form.reset();
            } else {
                showErrors(result.errors || {});
            }
        } catch (error) {
            console.error('Ошибка:', error);
            showSystemError();
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
        });

        // Клиентская валидация
        function validateFormClient(form) {
            const errors = {};
            const values = {
                fio: form.fio.value.trim(),
                tel: form.tel.value.trim().replace(/\s+/g, ''),
                email: form.email.value.trim(),
                bdate: form.bdate.value,
                gender: form.querySelector('input[name="gender"]:checked')?.value,
                languages: Array.from(form.languages.selectedOptions).map(o => o.value),
                bio: form.bio.value.trim(),
                ccheck: form.ccheck.checked
            };

            // ФИО
            if (!values.fio) errors.fio = 'ФИО обязательно';
            else if (values.fio.length > 150) errors.fio = 'Не более 150 символов';
            else if (!/^[\p{Cyrillic}\p{Latin}\s\-]+$/u.test(values.fio)) {
                errors.fio = 'Только буквы, пробелы и дефисы';
            }

            // Телефон
            if (!values.tel) errors.tel = 'Телефон обязателен';
            else if (!/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/.test(values.tel)) {
                errors.tel = 'Формат: +7(XXX)XXX-XX-XX';
            }

            // Email
            if (!values.email) errors.email = 'Email обязателен';
            else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email)) {
                errors.email = 'Неверный формат email';
            }

            // Дата рождения
            if (!values.bdate) errors.bdate = 'Дата обязательна';
            else {
                const today = new Date();
                const birthDate = new Date(values.bdate);
                const age = today.getFullYear() - birthDate.getFullYear();
                
                if (age < 18) errors.bdate = 'Возраст 18+';
                if (birthDate > today) errors.bdate = 'Дата в будущем';
            }

            // Пол
            if (!values.gender) errors.gender = 'Укажите пол';

            // Языки
            if (values.languages.length === 0) errors.languages = 'Выберите хотя бы 1 язык';

            // Биография
            if (values.bio.length > 5000) errors.bio = 'Не более 5000 символов';

            // Соглашение
            if (!values.ccheck) errors.ccheck = 'Необходимо согласие';

            return errors;
        }

        // Показать ошибки
        function showErrors(errors) {
            for (const [field, message] of Object.entries(errors)) {
                const errorElement = document.getElementById(`${field}-error`);
                const input = document.querySelector(`[name="${field}"]`) || 
                            document.getElementById(field);
                
                if (errorElement) errorElement.textContent = message;
                if (input) input.classList.add('error-input');
            }
        }

        // Показать успех
        function showSuccessMessage(data) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'alert alert-success';
            messageDiv.innerHTML = `
                <h4>Успешная регистрация!</h4>
                <p><strong>Логин:</strong> ${data.login}</p>
                <p><strong>Пароль:</strong> ${data.password}</p>
                <p>Сохраните эти данные</p>
            `;
            document.getElementById('form-messages').appendChild(messageDiv);
        }

        // Системная ошибка
        function showSystemError() {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'alert alert-danger';
            messageDiv.textContent = 'Произошла ошибка при отправке формы';
            document.getElementById('form-messages').appendChild(messageDiv);
        }
    </script>

    
</body>
</html>