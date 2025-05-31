<?php
$is_edit_mode = $c['is_edit_mode'] ?? false;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Форма</title>
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
<body>
    <div class="container">
        <div id="form-messages"></div>

        <div class="form-actions" style="justify-content: space-between; margin-bottom: 20px;margin-left: 55pt">
            <?php if (!$c['is_auth']): ?>
                <a href="./login.php" class="btn btn-secondary" target="_blank">Войти</a>
            <?php endif; ?>
            
            <a href="./modules/admin_panel.php" class="btn btn-admin" target="_blank" 
            style="background-color: #dc3545; color: white; border: none;">Администратор</a>
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
                <label for="phone">Телефон: <span class="required">*</span></label>
                <input type="tel" id="phone" name="phone" placeholder="+7 (XXX) XXX-XX-XX" required
                    value="<?php echo htmlspecialchars($c['values']['phone'] ?? ''); ?>"
                    class="<?php echo !empty($c['errors']['phone']) ? 'error-input' : ''; ?>">
                <div id="phone-error" class="error">
                    <?php echo !empty($c['errors']['phone']) ? htmlspecialchars($c['errors']['phone']) : ''; ?>
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
                <label for="birthdate">Дата рождения: <span class="required">*</span></label>
                <input type="date" id="birthdate" name="birthdate" required
                    value="<?php echo htmlspecialchars($c['values']['birthdate'] ?? ''); ?>"
                    class="<?php echo !empty($c['errors']['birthdate']) ? 'error-input' : ''; ?>">
                <div id="birthdate-error" class="error">
                    <?php echo !empty($c['errors']['birthdate']) ? htmlspecialchars($c['errors']['birthdate']) : ''; ?>
                </div>
            </div>

            <!-- Пол -->
            <div class="form-group">
                <label>Пол: <span class="required">*</span></label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="male" required <?php echo (isset($c['values']['gender']) && $c['values']['gender'] == 'male') ? 'checked' : ''; ?>>
                        <span class="form-check-label">Мужской</span></label>
                    <label><input type="radio" name="gender" value="female" required <?php echo (isset($c['values']['gender']) && $c['values']['gender'] == 'female') ? 'checked' : ''; ?>>
                        <span class="form-check-label">Женский</span></label>
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
                    <option value="1" <?php echo (isset($c['values']['languages']) && in_array(1, $c['values']['languages'])) ? 'selected' : ''; ?>>Pascal</option>
                    <option value="2" <?php echo (isset($c['values']['languages']) && in_array(2, $c['values']['languages'])) ? 'selected' : ''; ?>>C</option>
                    <option value="3" <?php echo (isset($c['values']['languages']) && in_array(3, $c['values']['languages'])) ? 'selected' : ''; ?>>C++</option>
                    <option value="4" <?php echo (isset($c['values']['languages']) && in_array(4, $c['values']['languages'])) ? 'selected' : ''; ?>>JavaScript</option>
                    <option value="5" <?php echo (isset($c['values']['languages']) && in_array(5, $c['values']['languages'])) ? 'selected' : ''; ?>>PHP</option>
                    <option value="6" <?php echo (isset($c['values']['languages']) && in_array(6, $c['values']['languages'])) ? 'selected' : ''; ?>>Python</option>
                    <option value="7" <?php echo (isset($c['values']['languages']) && in_array(7, $c['values']['languages'])) ? 'selected' : ''; ?>>Java</option>
                    <option value="8" <?php echo (isset($c['values']['languages']) && in_array(8, $c['values']['languages'])) ? 'selected' : ''; ?>>Haskell</option>
                    <option value="9" <?php echo (isset($c['values']['languages']) && in_array(9, $c['values']['languages'])) ? 'selected' : ''; ?>>Clojure</option>
                    <option value="10" <?php echo (isset($c['values']['languages']) && in_array(10, $c['values']['languages'])) ? 'selected' : ''; ?>>Prolog</option>
                    <option value="11" <?php echo (isset($c['values']['languages']) && in_array(11, $c['values']['languages'])) ? 'selected' : ''; ?>>Scala</option>
                    <option value="12" <?php echo (isset($c['values']['languages']) && in_array(12, $c['values']['languages'])) ? 'selected' : ''; ?>>Go</option>
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
                    <input type="checkbox" id="agreement" name="agreement" value="1" required class="form-check-input" <?php echo (!empty($c['values']['agreement'])) ? 'checked' : ''; ?>>
                    Я согласен(а) с условиями контракта <span class="required">*</span>
                </label>
                <div id="agreement-error" class="error">
                    <?php echo !empty($c['errors']['agreement']) ? htmlspecialchars($c['errors']['agreement']) : ''; ?>
                </div>
            </div>

            <button type="submit" id="submit-btn" class="btn-danger">Сохранить</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('registration-form');
            const submitBtn = document.getElementById('submit-btn');
            const formMessages = document.getElementById('form-messages');

            // Валидация формы перед отправкой
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                
                // Очищаем предыдущие сообщения и ошибки
                formMessages.innerHTML = '';
                clearErrors();
                
                // Проверяем валидность формы
                if (!validateForm()) {
                    return;
                }
                
                // Блокируем кнопку отправки
                const originalBtnText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Отправка...';
                
                // Собираем данные формы
                const formData = new FormData(form);
                
                // Для множественного выбора языков
                const languagesSelect = document.getElementById('languages');
                const selectedLanguages = Array.from(languagesSelect.selectedOptions).map(option => option.value);
                formData.delete('languages[]');
                selectedLanguages.forEach(lang => formData.append('languages[]', lang));
                
                // Отправляем AJAX-запрос
                fetch('/web-backend/web/register', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(handleResponse)
                .then(handleSuccess)
                .catch(handleError)
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                });
            });
            
            // Функция валидации формы
            function validateForm() {
                let isValid = true;
                
                // Проверка ФИО
                const fio = document.getElementById('fio').value.trim();
                if (!fio) {
                    showError('fio', 'ФИО обязательно для заполнения');
                    isValid = false;
                } else if (!/^[а-яА-ЯёЁa-zA-Z\s\-]+$/u.test(fio)) {
                    showError('fio', 'ФИО должно содержать только буквы, пробелы и дефисы');
                    isValid = false;
                } else if (fio.length > 200) {
                    showError('fio', 'ФИО не должно превышать 200 символов');
                    isValid = false;
                }
                
                // Проверка телефона
                const phone = document.getElementById('phone').value.trim();
                if (!phone) {
                    showError('phone', 'Телефон обязателен для заполнения');
                    isValid = false;
                } else if (!/^\+7\s?\(?\d{3}\)?\s?\d{3}-?\d{2}-?\d{2}$/.test(phone)) {
                    showError('phone', 'Введите телефон в формате +7 (XXX) XXX-XX-XX');
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
                } else if (email.length > 100) {
                    showError('email', 'Email не должен превышать 100 символов');
                    isValid = false;
                }
                
                // Проверка даты рождения
                const birthdate = document.getElementById('birthdate').value;
                if (!birthdate) {
                    showError('birthdate', 'Дата рождения обязательна');
                    isValid = false;
                } else {
                    const birthDate = new Date(birthdate);
                    const now = new Date();
                    const age = now.getFullYear() - birthDate.getFullYear();
                    
                    if (age < 18) {
                        showError('birthdate', 'Возраст должен быть 18+ лет');
                        isValid = false;
                    } else if (age > 120) {
                        showError('birthdate', 'Проверьте дату рождения');
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
                if (bio.length > 1000) {
                    showError('bio', 'Биография не должна превышать 1000 символов');
                    isValid = false;
                }
                
                // Проверка соглашения
                const agreement = document.getElementById('agreement').checked;
                if (!agreement) {
                    showError('agreement', 'Необходимо принять соглашение');
                    isValid = false;
                }
                
                return isValid;
            }
            
            // Обработка ответа сервера
            function handleResponse(response) {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP error! status: ${response.status}. Response: ${text}`);
                    });
                }
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Received non-JSON response:', text);
                        throw new TypeError("Ожидался JSON, но получен " + contentType);
                    });
                }
                return response.json();
            }
            
            // Обработка успешного ответа
            function handleSuccess(data) {
                if (data.success) {
                    if (data.credentials) {
                        // Показываем логин и пароль для нового пользователя
                        showSuccessMessage(`
                            <h4>Регистрация успешна!</h4>
                            <p><strong>Логин:</strong> ${data.credentials.login}</p>
                            <p><strong>Пароль:</strong> ${data.credentials.password}</p>
                            <p class="text-danger">Запишите эти данные, они больше не будут показаны!</p>
                            <a href="login.php" class="btn btn-success mt-2">Войти в систему</a>
                        `);
                    } else {
                        // Просто сообщение об успехе для авторизованных пользователей
                        showSuccessMessage(data.message || 'Данные успешно сохранены');
                    }
                    
                    // Перенаправление после задержки
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 3000);
                    }
                } else {
                    // Показываем ошибки валидации
                    showValidationErrors(data.errors);
                }
            }
            
            // Обработка ошибок
            function handleError(error) {
                console.error('Ошибка при отправке формы:', error);
                
                if (error instanceof TypeError && error.message.includes('JSON')) {
                    showErrorMessage('Сервер вернул некорректный ответ. Пожалуйста, сообщите администратору.');
                } else {
                    showErrorMessage('Произошла ошибка при отправке формы. Пожалуйста, попробуйте ещё раз.');
                }
            }
            
            // Вспомогательные функции
            function clearErrors() {
                document.querySelectorAll('.error').forEach(el => el.textContent = '');
                document.querySelectorAll('.error-input').forEach(el => el.classList.remove('error-input'));
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
                
                // Для чекбокса соглашения
                if (field === 'agreement') {
                    const agreementLabel = document.querySelector('label[for="agreement"]');
                    if (agreementLabel) {
                        agreementLabel.classList.add('error');
                    }
                }
            }
            
            function showSuccessMessage(message) {
                const div = document.createElement('div');
                div.className = 'alert alert-success';
                div.innerHTML = message;
                formMessages.appendChild(div);
                formMessages.scrollIntoView({ behavior: 'smooth' });
            }
            
            function showErrorMessage(message) {
                const div = document.createElement('div');
                div.className = 'alert alert-danger';
                div.textContent = message;
                formMessages.appendChild(div);
                formMessages.scrollIntoView({ behavior: 'smooth' });
            }
            
            function showValidationErrors(errors) {
                for (const field in errors) {
                    showError(field, errors[field]);
                }
                
                // Фокусируемся на первом ошибочном поле
                const firstErrorField = Object.keys(errors)[0];
                if (firstErrorField) {
                    const firstErrorInput = document.querySelector(`[name="${firstErrorField}"], [name="${firstErrorField}[]"]`);
                    if (firstErrorInput) {
                        firstErrorInput.focus();
                    }
                }
            }
        });
    </script>
</body>
</html>