<?php
session_start();
include('./includes/db.php'); // Подключение к БД
include('./includes/functions.php');

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Получение информации о пользователе
$user = get_user_data($conn, $user_id);

?>

<style>
    /* ----------- Общее ----------- */
    * {
        margin: 0;
        padding: 0;
        list-style: none;
        border: 0;
        outline: 0;
        -webkit-tap-highlight-color: transparent;
        text-decoration: none;
        box-sizing: border-box;
    }

    body {
        font-family: "Raleway", sans-serif;
        background: linear-gradient(to top, #588157 0%, #A3B18A 100%);
        height: auto;
        background-attachment: fixed;
        background-size: cover;
    }

    /* ----------- Шапка ----------- */
    .mainNav {
        width: 100%;
        height: 80px;
        position: absolute;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #2e1c0d;
        text-transform: uppercase;
        padding: 0 40px;
    }

    .mainNav__logo {
        font-weight: 800;
        letter-spacing: 1px;
        font-size: 18px;
    }

    .mainNav__links {
        display: flex;
        color: #2e1c0d;
    }

    .mainNav__link {
        color: #2e1c0d;
        letter-spacing: 1px;
        font-size: 14px;
        margin-left: 20px;
        font-weight: 600;
        box-shadow: inset 0px -10px 0px rgba(255, 255, 255, 0.5);
        transition: all 0.4s ease, transform 0.2s ease;
        padding: 2px 4px;
        transform: translateY(0px);
    }

    .mainNav__link:hover {
        color: #2e1c0d;
        transform: translateY(-5px);
        box-shadow: inset 0px -20px 0px #DAD7CD;
    }

    /* ----------- Сама форма ----------- */
    .tab {
        background: #588157d5;
        padding: 40px 50px;
        position: relative;
        box-shadow: 0 5px 15px #3a5a40c1;
    }

    .container {
        padding-top: 10%;
        padding-bottom: 15%;
    }

    .form-horizontal .form-group {
        margin: 0 0 15px 0;
        position: relative;
    }

    .form-horizontal .form-control {
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        border-radius: 0px;
        box-shadow: none;
        padding: 0 20px;
        font-size: 14px;
        font-weight: bold;
        color: #2e1c0d;
        transition: all 0.3s ease 0s;
    }

    .form-horizontal .form-control:focus {
        box-shadow: none;
        outline: 0 none;
    }

    .form-horizontal .form-group label {
        padding: 0 20px;
        color: #2e1c0d;
        text-transform: capitalize;
        margin-bottom: 10px;
    }

    .form-horizontal .btn {
        width: 100%;
        background: #344E41;
        padding: 10px 20px;
        border: none;
        font-size: 14px;
        font-weight: bold;
        color: #2e1c0d;
        border-radius: 0px;
        text-transform: uppercase;
        margin: 20px 0 30px 0;
    }

    .form-horizontal .btn:focus {
        background: #344E41;
        color: #DAD7CD;
        outline: none;
        box-shadow: none;
    }

    /* ----------- Радио-кнопка ----------- */
    .radio-group {
        display: flex;
        align-items: center;
    }

    .radio-group label {
        margin-right: 15px;
        display: flex;
        align-items: center;
    }

    .radio-group input[type="radio"] {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        width: 27px;
        height: 20px;
        border: 1px solid #344E41;
        border-radius: 50%;
        outline: none;
        cursor: pointer;
        position: relative;
        margin-right: 5px;
    }

    .radio-group input[type="radio"]:checked,
    .radio-group input[type="radio"] {
        background-color: #819979;
    }

    .radio-group.radio-error input[type="radio"] {
        border-color: red;
    }

    .radio-group input[type="radio"]:checked::after {
        content: '';
        display: block;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 8px;
        height: 8px;
        background-color: #344E41;
        border-radius: 50%;
    }
</style>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Редактировать профиль</title>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>

<body>
    <!-- Шапка -->
    <nav class="mainNav">
        <div class="mainNav__logo">SkillSwap</div>
        <div class="mainNav__links">
            <a href="index.php" class="mainNav__link">Главная страница</a>
            <a href="user.php" class="mainNav__link">Профиль</a>
            <a href="logout.php" class="mainNav__link">Выход</a>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-offset-3 col-md-6">
                <div class="tab" role="tabpanel">
                    <div class="tab-content tabs">
                        <div role="tabpanel" class="tab-pane fade in active" id="Section1">
                            <?php if ($user): ?>
                                <form class="form-horizontal" action="actions/update_profile.php" method="post">
                                    <div class="form-group">
                                        <label>Имя:</label>
                                        <input type="text" class="form-control" name="first_name"
                                            value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Фамилия:</label>
                                        <input type="text" class="form-control" name="last_name"
                                            value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Почта:</label>
                                        <input type="email" class="form-control" name="email"
                                            value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Дата рождения:</label>
                                        <input type="date" class="form-control" name="birthdate"
                                            value="<?php echo htmlspecialchars($user['birthdate']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Пол:</label>
                                        <div class="radio-group">
                                            <label>
                                                <input class="radio" name="gender" type="radio" value="Female" <?php if ($user['gender'] == 'Female')
                                                    echo 'checked'; ?> />Женский
                                            </label>
                                            <label>
                                                <input class="radio" name="gender" type="radio" value="Male" <?php if ($user['gender'] == 'Male')
                                                    echo 'checked'; ?> />Мужской
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-default">Сохранить изменения</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <p>Ошибка: Не удалось получить информацию о пользователе.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
