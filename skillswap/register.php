
<?
session_start();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title> Регистрация </title>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/parallax/3.1.0/parallax.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/parallax/3.1.0/parallax.js'></script>
    <script src="./style/script.js"></script>
</head>

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

<body>
    <!-- Шапка -->
    <nav class="mainNav">
        <div class="mainNav__logo">SkillSwap</div>
        <div class="mainNav__links">
            <a href="index.php" class="mainNav__link">Главная страница</a>
            <a href="login.php" class="mainNav__link">Вход</a>
        </div>
    </nav>

    <!-- Общее -->
    <div class="container">
        <div class="row">
            <div class="col-md-offset-3 col-md-6">
                <div class="tab" role="tabpanel">
                    <div class="tab-content tabs">
                        <div role="tabpanel" class="tab-pane fade in active" id="Section1">
                            <form class="form-horizontal" action="actions/register_process.php" method="post">
                                <div class="form-group">
                                    <label>Логин:</label>
                                    <input type="text" class="form-control" name="login" required>
                                </div>
                                <div class="form-group">
                                    <label>Пароль:</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                                <div class="form-group">
                                    <label>Имя:</label>
                                    <input type="text" class="form-control" name="first_name" required>
                                </div>
                                <div class="form-group">
                                    <label>Фамилия:</label>
                                    <input type="text" class="form-control" name="last_name" required>
                                </div>
                                <div class="form-group">
                                    <label>Почта:</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="form-group">
                                    <label>Дата рождения:</label>
                                    <input type="date" class="form-control" name="birthdate">
                                </div>
                                <div class="form-group">
                                    <label>Пол:</label>
                                    <div class="radio-group">
                                        <label>
                                            <input class="radio" name="gender" type="radio" value="Female" checked />Женский
                                        </label>
                                        <label>
                                            <input class="radio" name="gender" type="radio" value="Male" />Мужской
                                        </label>
                                    </div>
                                </div>

                                <!-- Поля для навыков -->
                                <div id="skill-fields">
                                    <div class="skill-group">
                                        <div class="form-group">
                                            <label>Ваш навык:</label>
                                            <input type="text" class="form-control" name="skill_names[]" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Опишите подробнее ваш навык:</label>
                                            <textarea class="form-control" name="skill_descriptions[]" style="resize: vertical;"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" id="add-skill" class="btn btn-default">Добавить навык</button>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-default">Зарегистрироваться</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- Скрипт для добавления полей навыков -->
    <script>
        $(document).ready(function() {
            $("#add-skill").click(function() {
                $("#skill-fields").append(`
                    <div class="skill-group">
                        <div class="form-group">
                            <label>Ваш навык:</label>
                            <input type="text" class="form-control" name="skill_names[]" required>
                        </div>
                        <div class="form-group">
                            <label>Опишите подробнее ваш навык:</label>
                            <textarea class="form-control" name="skill_descriptions[]" style="resize: vertical;"></textarea>
                        </div>
                    </div>
                `);
            });
        });
    </script>
</body>

</html>
