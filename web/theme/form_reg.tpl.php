<!DOCTYPE html>
<html>

<head>
    <title>Форма регистрации</title>
    <style>
        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }

        .error-input {
            border-color: red !important;
        }
    </style>
</head>

<body>
    <div class="container">
        <div id="form-messages"></div>

        <div class="form-actions" style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <?php if (!$c['is_auth']): ?>
                <a href="./login.php" class="btn btn-secondary">Войти</a>
            <?php endif; ?>
            
            <a href="/admin" class="btn btn-admin" target="_blank" 
            style="background-color: #dc3545; color: white; border: none;"> Администратор</a>
        </div>

        <form method="post" id="registration-form" class="contact-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($c['csrf_token']); ?>">

            <!-- ФИО -->
            <div class="form-group">
                <label for="fio">ФИО: <span class="required">*</span></label>
                <input type="text" id="fio" name="fio"
                    value="<?php echo htmlspecialchars($c['values']['fio'] ?? ''); ?>"
                    class="<?php echo !empty($c['errors']['fio']) ? 'error-input' : ''; ?>">
                <div id="fio-error" class="error">
                    <?php echo !empty($c['errors']['fio']) ? htmlspecialchars($c['errors']['fio']) : ''; ?>
                </div>
            </div>

            <!-- Телефон -->
            <div class="form-group">
                <label for="phone">Телефон: <span class="required">*</span></label>
                <input type="text" id="phone" name="phone" placeholder="+7 (XXX) XXX-XX-XX"
                    value="<?php echo htmlspecialchars($c['values']['phone'] ?? ''); ?>"
                    class="<?php echo !empty($c['errors']['phone']) ? 'error-input' : ''; ?>">
                <div id="phone-error" class="error">
                    <?php echo !empty($c['errors']['phone']) ? htmlspecialchars($c['errors']['phone']) : ''; ?>
                </div>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email: <span class="required">*</span></label>
                <input type="text" id="email" name="email" placeholder="example@domain.com"
                    value="<?php echo htmlspecialchars($c['values']['email'] ?? ''); ?>"
                    class="<?php echo !empty($c['errors']['email']) ? 'error-input' : ''; ?>">
                <div id="email-error" class="error">
                    <?php echo !empty($c['errors']['email']) ? htmlspecialchars($c['errors']['email']) : ''; ?>
                </div>
            </div>

            <!-- Дата рождения -->
            <div class="form-group">
                <label for="birthdate">Дата рождения: <span class="required">*</span></label>
                <input type="text" id="birthdate" name="birthdate" placeholder="YYYY-MM-DD"
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
                    <label><input type="radio" name="gender" value="male" class="form-check-input" <?php echo (isset($c['values']['gender']) && $c['values']['gender'] == 'male') ? 'checked' : ''; ?>>
                        <span class="form-check-label">Мужской</span></label>
                    <label><input type="radio" name="gender" value="female" class="form-check-input" <?php echo (isset($c['values']['gender']) && $c['values']['gender'] == 'female') ? 'checked' : ''; ?>>
                        <span class="form-check-label">Женский</span></label>
                </div>
                <div id="gender-error" class="error">
                    <?php echo !empty($c['errors']['gender']) ? htmlspecialchars($c['errors']['gender']) : ''; ?>
                </div>
            </div>

            <!-- Языки программирования -->
            <div class="form-group">
                <label for="languages">Любимые языки программирования: <span class="required">*</span></label>
                <select id="languages" name="languages[]" multiple size="6"
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
                <label class="form-check-label">
                    <input type="checkbox" id="agreement" name="agreement" value="1" class="form-check-input" <?php echo (!empty($c['values']['agreement'])) ? 'checked' : ''; ?>>
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

            // Маска для телефона
            const phoneInput = document.getElementById('phone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function (e) {
                    let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
                    e.target.value = !x[2] ? x[1] : x[1] + ' (' + x[2] + ') ' + x[3] + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
                });
            }

            // Маска для даты рождения
            const birthdateInput = document.getElementById('birthdate');
            if (birthdateInput) {
                birthdateInput.addEventListener('input', function (e) {
                    let x = e.target.value.replace(/\D/g, '').match(/(\d{0,4})(\d{0,2})(\d{0,2})/);
                    e.target.value = !x[2] ? x[1] : x[1] + '-' + x[2] + (x[3] ? '-' + x[3] : '');
                });
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                // Очищаем предыдущие сообщения и ошибки
                formMessages.innerHTML = '';
                clearErrors();

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
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => {
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
                    })
                    .then(data => {
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
                    })
                    .catch(error => {
                        console.error('Ошибка при отправке формы:', error);

                        if (error instanceof TypeError && error.message.includes('JSON')) {
                            showErrorMessage('Сервер вернул некорректный ответ. Пожалуйста, сообщите администратору.');
                        } else {
                            showErrorMessage('Произошла ошибка при отправке формы. Пожалуйста, попробуйте ещё раз.');
                        }
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalBtnText;
                    });
            });

            // Функции для работы с сообщениями и ошибками
            function clearErrors() {
                document.querySelectorAll('.error').forEach(el => el.textContent = '');
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
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
                    const errorElement = document.getElementById(`${field}-error`);
                    let inputElement = document.querySelector(`[name="${field}"], [name="${field}[]"]`);

                    if (!inputElement) {
                        // Для чекбоксов и радио-кнопок
                        inputElement = document.querySelector(`input[name="${field}"]`);
                    }

                    if (errorElement) {
                        errorElement.textContent = errors[field];
                    }

                    if (inputElement) {
                        inputElement.classList.add('is-invalid');

                        // Для чекбокса соглашения
                        if (field === 'agreement') {
                            const agreementLabel = inputElement.closest('.form-check-label');
                            if (agreementLabel) {
                                agreementLabel.classList.add('text-danger');
                            }
                        }
                    }
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