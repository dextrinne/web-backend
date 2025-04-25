<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title> Логин </title>
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

    *:focus {
        outline: 0;
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
    .container {
        padding-top: 10%;
        padding-bottom: 15%;
    }

    a:hover,
    a:focus {
        outline: none;
        text-decoration: none;
    }

    .tab {
        background: #588157d5;
        padding: 40px 50px;
        position: relative;
        box-shadow: 0 5px 15px #3a5a40c1;
    }

    .tab:before {
        content: "";
        width: 100%;
        height: 100%;
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0.85;
    }

    .tab .nav-tabs {
        border-bottom: none;
        padding: 0 20px;
        position: relative;
    }

    .tab .nav-tabs li {
        margin: 0 30px 0 0;
        color: #2e1c0d;
    }

    .tab .nav-tabs li a {
        font-size: 18px;
        color: #2e1c0d;
        border-radius: 0;
        text-transform: uppercase;
        font-weight: bold;
        padding: 0;
        margin-right: 0;
        border: none;
        opacity: 0.5;
        position: relative;
        transition: all 0.5s ease 0s;
    }

    .tab .nav-tabs li.active a,
    .tab .nav-tabs li.active a:focus,
    .tab .nav-tabs li.active a:hover {
        border: none;
        background: transparent;
        opacity: 1;
        border-bottom: 2px solid #344E41;
        color: #2e1c0d;
    }

    .tab .tab-content {
        padding: 20px 0 0 0;
        margin-top: 40px;
        background: transparent;
        z-index: 1;
        position: relative;
    }

    .form-horizontal .form-group {
        margin: 0 0 15px 0;
        position: relative;
    }

    .form-horizontal .form-control {
        height: 40px;
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

    .form-horizontal input {
        background: #819979;
    }
</style>

<body>
    <!-- Шапка -->
    <nav class="mainNav">
        <div class="mainNav__logo">SkillSwap</div>
        <div class="mainNav__links">
            <a href="index.php" class="mainNav__link">Главная страница</a>
            <a href="register.php" class="mainNav__link">Регистрация</a>
        </div>
    </nav>

    <!-- Общее -->
    <div class="container">
        <div class="row">
            <div class="col-md-offset-3 col-md-6">
                <div class="tab" role="tabpanel">
                    <!-- Вход -->
                    <div class="tab-content tabs">
                        <div role="tabpanel" class="tab-pane fade in active" id="Section1">
                            <form class="form-horizontal" action="actions/login_process.php" method="post">
                                <div class="form-group">
                                    <label>Логин:</label>
                                    <input type="text" class="form-control" name="login" required>
                                </div>
                                <div class="form-group">
                                    <label>Пароль:</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-default">Войти</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>

</html>
