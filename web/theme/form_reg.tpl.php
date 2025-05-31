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
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registration-form');
            const submitBtn = document.getElementById('submit-btn');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Очищаем предыдущие сообщения и ошибки
                document.getElementById('form-messages').innerHTML = '';
                clearErrors();
                
                // Проверяем валидность формы
                if (!validateForm()) {
                    return;
                }
                
                // Блокируем кнопку отправки
                submitBtn.disabled = true;
                submitBtn.textContent = 'Отправка...';
                
                // Собираем данные формы
                const formData = new FormData(form);
                
                // Для множественного выбора языков
                const languagesSelect = document.getElementById('languages');
                const selectedLanguages = Array.from(languagesSelect.selectedOptions).map(option => option.value);
                formData.delete('languages[]');
                selectedLanguages.forEach(lang => formData.append('languages[]', lang));
                
                // Определяем URL для отправки
                const url = '<?php echo $c['is_auth'] ? "edit_user.php?id=" . ($c['user']['id'] ?? '') : "form.php"; ?>';
                
                fetch('/web-backend/web/form_reg.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showSuccessMessage();
                        document.getElementById('registration-form').reset();
                    }
                })
                .catch(error => console.error('Error:', error));

                function showSuccessMessage() {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'registration-alert success';
                    alertDiv.innerHTML = `
                        <h3>Регистрация успешна!</h3>
                        <p>Ваши данные для входа:</p>
                        <p><strong>Логин:</strong> ${generatedLogin}</p>
                        <p><strong>Пароль:</strong> ${generatedPassword}</p>
                        <p>Сохраните эти данные!</p>
                        <button onclick="this.parentElement.remove()">Закрыть</button>
                    `;
                    document.body.appendChild(alertDiv);
                    setTimeout(() => alertDiv.remove(), 10000);
                }
                .catch(error => {
                showMessage('error', 'Ошибка: ' + error.message);
                });
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = '<?php echo $c['is_auth'] ? "Обновить данные" : "Зарегистрироваться"; ?>';
                });
            });
            
            function clearErrors() {
                document.querySelectorAll('.error').forEach(el => el.textContent = '');
                document.querySelectorAll('.error-input').forEach(el => el.classList.remove('error-input'));
            }
            
            function showMessage(type, message) {
                const div = document.createElement('div');
                div.className = `alert alert-${type}`;
                div.innerHTML = message;
                document.getElementById('form-messages').appendChild(div);
            }
            
            function showValidationErrors(errors) {
                for (const field in errors) {
                    const errorElement = document.getElementById(`${field}-error`);
                    const inputElement = document.querySelector(`[name="${field}"], [name="${field}[]"]`);
                    
                    if (errorElement) {
                        errorElement.textContent = errors[field];
                    }
                    
                    if (inputElement) {
                        inputElement.classList.add('error-input');
                    }
                }
            }
            
            function validateForm() {
                let isValid = true;
                
                // Проверка ФИО
                const fio = document.getElementById('fio').value.trim();
                if (!fio) {
                    showError('fio', 'ФИО обязательно для заполнения');
                    isValid = false;
                } else if (fio.length > 150) {
                    showError('fio', 'ФИО не должно превышать 150 символов');
                    isValid = false;
                }
                
                // Проверка телефона
                const tel = document.getElementById('tel').value.trim();
                if (!tel) {
                    showError('tel', 'Телефон обязателен для заполнения');
                    isValid = false;
                } else if (!/^\+7\s?\(?\d{3}\)?\s?\d{3}-?\d{2}-?\d{2}$/.test(tel)) {
                    showError('tel', 'Введите телефон в формате +7 (XXX) XXX-XX-XX');
                    isValid = false;
                }
                
                // Проверка email
                const email = document.getElementById('email').value.trim();
                if (!email) {
                    showError('email', 'Email обязателен для заполнения');
                    isValid = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    showError('email', 'Введите корректный email адрес');
                    isValid = false;
                } else if (email.length > 255) {
                    showError('email', 'Email не должен превышать 255 символов');
                    isValid = false;
                }
                
                // Проверка даты рождения
                const bdate = document.getElementById('bdate').value;
                if (!bdate) {
                    showError('bdate', 'Дата рождения обязательна');
                    isValid = false;
                } else {
                    const birthDate = new Date(bdate);
                    const now = new Date();
                    const age = now.getFullYear() - birthDate.getFullYear();
                    
                    if (age < 18) {
                        showError('bdate', 'Возраст должен быть 18+ лет');
                        isValid = false;
                    } else if (age > 120) {
                        showError('bdate', 'Проверьте дату рождения');
                        isValid = false;
                    }
                }
                
                // Проверка пола
                const gender = document.querySelector('input[name="gender"]:checked');
                if (!gender) {
                    showError('gender', 'Укажите пол');
                    isValid = false;
                }
                
                // Проверка языков программирования
                const languages = document.getElementById('languages');
                const selectedLanguages = Array.from(languages.selectedOptions).map(option => option.value);
                if (selectedLanguages.length === 0) {
                    showError('languages', 'Выберите хотя бы один язык');
                    isValid = false;
                }
                
                // Проверка биографии
                const bio = document.getElementById('bio').value.trim();
                if (bio.length > 5000) {
                    showError('bio', 'Биография не должна превышать 5000 символов');
                    isValid = false;
                }
                
                // Проверка соглашения
                const ccheck = document.getElementById('ccheck').checked;
                if (!ccheck) {
                    showError('ccheck', 'Необходимо принять соглашение');
                    isValid = false;
                }
                
                return isValid;
            }
            
            function showError(field, message) {
                const errorElement = document.getElementById(`${field}-error`);
                const inputElement = document.querySelector(`[name="${field}"], [name="${field}[]"]`);
                
                if (errorElement) {
                    errorElement.textContent = message;
                }
                
                if (inputElement) {
                    inputElement.classList.add('error-input');
                }
            }
        });
    </script>
</body>
</html>