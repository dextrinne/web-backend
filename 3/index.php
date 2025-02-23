<?php
header('Content-Type: text/html; charset=UTF-8');
echo "<link rel='stylesheet' href='style.css'>";

// Подключение к базе данных
$user = 'u68595';
$pass = '6788124';

try {
    $db = new PDO('mysql:host=localhost;dbname=u68595;charset=utf8', $user, $pass, [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $db->exec("SET NAMES utf8"); // Установка кодировки соединения
} catch (PDOException $e) {
    print('Ошибка соединения с БД: ' . $e->getMessage());
    exit();
}

// Функция для получения списка языков программирования
function getAbilities($db) {
    try {
        $abilities = [];
        $data = $db->query("SELECT id, name FROM abilities")->fetchAll(PDO::FETCH_KEY_PAIR);
        return $abilities;
    } catch (PDOException $e) {
        print('Ошибка получения списка языков: ' . $e->getMessage());
        exit();
    }
}

// Получаем список языков программирования ДО обработки формы
$abilities = getAbilities($db);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['submit'])) {
        print('Спасибо, результаты сохранены.');
    }
    include('form.php');
    exit();
}

// Валидация данных
$errors = FALSE;

if (empty($_POST['fio'])) {
    print('Заполните имя.<br/>');
    $errors = TRUE;
} elseif (strlen($_POST['fio']) > 150) {
    print("ФИО не должно превышать 150 символов.<br>");
    $errors = TRUE;
} elseif (!preg_match("/^[a-zA-Zа-яА-ЯёЁs]+$/u", $_POST['fio'])) {
    print("ФИО должно содержать только буквы и пробелы.<br>");
    $errors = TRUE;
}

if (empty($_POST['tel']) || !preg_match('/^+7d{10}$/', $_POST['tel'])) {
    print('Введите корректный номер телефона.<br/>');
    $errors = TRUE;
}

if (empty($_POST["email"]) || !filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    print('Введите корректный email.<br/>');
    $errors = TRUE;
}

if (empty($_POST['abilities'])) {
    print('Выберите любимый язык программирования.<br/>');
    $errors = TRUE;
} else {
    foreach ($_POST['abilities'] as $ability) {
        if (!array_key_exists($ability, $abilities)) {
            print('Выберите любимый язык программирования из списка.<br/>');
            $errors = TRUE;
            break;
        }
    }
}

if (empty($_POST['bdate']) || !preg_match('/^d{4}-d{2}-d{2}$/', $_POST['bdate'])) {
    print("Введите корректную дату рождения.<br>");
    $errors = TRUE;
}

if (empty($_POST['radio']) || !($_POST['radio'] == "female" || $_POST['radio'] == "male")) {
    print('Выберите пол.<br/>');
    $errors = TRUE;
}

if (empty($_POST['bio'])) {
    print('Заполните биографию.<br/>');
    $errors = TRUE;
}

if (!isset($_POST["ccheck"])) {
    print('Подтвердите ознакомление с контрактом.<br/>');
    $errors = TRUE;
}

if ($errors) {
    exit();
}

// Сохранение данных в базу данных
try {
    $stmt = $db->prepare("INSERT INTO users (fio, tel, email, gender, bdate, bio, ccheck) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['fio'], $_POST['tel'], $_POST['email'], $_POST['radio'], $_POST['bdate'], $_POST['bio'], (isset($_POST["ccheck"]) ? 1 : 0)]);

    $user_id = $db->lastInsertId();

    // Связываем пользователя с выбранными языками
    $stmt = $db->prepare("INSERT INTO user (user_id, lang_id) VALUES (?, ?)"); 
    foreach ($_POST['abilities'] as $ability_id) {
        $stmt->execute([$user_id, $ability_id]);
    }

    header('Location: ?submit=1'); 

} catch (PDOException $e) {
    print('Ошибка сохранения в БД: ' . $e->getMessage());
    exit();
}
?>
