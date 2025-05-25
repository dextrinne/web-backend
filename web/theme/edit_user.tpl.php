<?php
$user = $c['user'] ?? [];
$all_languages = $c['all_languages'] ?? [];
$selected_lang_ids = $c['selected_lang_ids'] ?? [];
$csrf_token = $c['csrf_token'] ?? '';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование пользователя</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="date"],
        textarea,
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        .languages-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }
        .language-item {
            display: flex;
            align-items: center;
        }
        .language-item input {
            width: auto;
            margin-right: 5px;
        }
        .form-actions {
            margin-top: 20px;
            text-align: right;
        }
        button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .save-btn {
            background-color: #4CAF50;
            color: white;
        }
        .cancel-btn {
            background-color: #f44336;
            color: white;
            margin-right: 10px;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            display: none;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Редактирование пользователя #<?= htmlspecialchars($user['id'] ?? '') ?></h1>
        
        <div id="message" class="message"></div>
        
        <form id="edit-user-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <div class="form-group">
                <label for="fio">ФИО:</label>
                <input type="text" id="fio" name="fio" value="<?= htmlspecialchars($user['fio'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="tel">Телефон:</label>
                <input type="text" id="tel" name="tel" value="<?= htmlspecialchars($user['tel'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="bdate">Дата рождения:</label>
                <input type="date" id="bdate" name="bdate" value="<?= htmlspecialchars($user['bdate'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="gender">Пол:</label>
                <select id="gender" name="gender" required>
                    <option value="Male" <?= ($user['gender'] ?? '') == 'Male' ? 'selected' : '' ?>>Мужской</option>
                    <option value="Female" <?= ($user['gender'] ?? '') == 'Female' ? 'selected' : '' ?>>Женский</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="bio">Биография:</label>
                <textarea id="bio" name="bio"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="ccheck" <?= ($user['ccheck'] ?? 0) ? 'checked' : '' ?>>
                    Согласен с условиями
                </label>
            </div>
            
            <div class="form-group">
                <label>Языки программирования:</label>
                <div class="languages-container">
                    <?php foreach ($all_languages as $lang): ?>
                        <div class="language-item">
                            <input type="checkbox" name="languages[]" value="<?= $lang['id'] ?>" 
                                <?= in_array($lang['id'], $selected_lang_ids) ? 'checked' : '' ?>>
                            <label><?= htmlspecialchars($lang['name']) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="cancel-btn" onclick="window.close()">Закрыть</button>
                <button type="submit" class="save-btn">Сохранить</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('edit-user-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const messageDiv = document.getElementById('message');
            
            // Для множественного выбора языков
            const languages = [];
            document.querySelectorAll('input[name="languages[]"]:checked').forEach(checkbox => {
                languages.push(checkbox.value);
            });
            
            // Удаляем старые значения и добавляем новые
            formData.delete('languages[]');
            languages.forEach(lang => formData.append('languages[]', lang));
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                messageDiv.textContent = data.message;
                messageDiv.className = 'message ' + (data.success ? 'success' : 'error');
                messageDiv.style.display = 'block';
                
                if (data.success) {
                    setTimeout(() => {
                        window.opener.location.reload();
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.textContent = 'Произошла ошибка при отправке формы';
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
            });
        });
    </script>
</body>
</html>