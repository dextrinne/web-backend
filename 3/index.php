<?php
header('Content-Type: text/html; charset=UTF-8');
echo "<link rel='stylesheet' href='style.css'>";

$user = 'u68595';
$pass = '6788124';
try {
    $db = new PDO('mysql:host=localhost;dbname=u68595', $user, $pass,
        [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    print('Ошибка соединения с БД : ' . $e->getMessage());
    exit();
}


function getAbilities($db) {
    try {
        $abilities = [];
        $data = $db->query("SELECT id, name FROM abilities")->fetchAll(PDO::FETCH_KEY_PAIR); // Changed to FETCH_KEY_PAIR
        return $abilities;
    } catch (PDOException $e) {
        print('Error: ' . $e->getMessage());
        exit();
    }
}

$abilities = getAbilities($db);  // Получаем список языков из БД

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['save'])) {
        print('Спасибо, результаты сохранены.');
    }
    // Включаем содержимое файла form.php.
    include('form.php');
    exit();
}

// Проверяем на наличие ошибок.
$errors = FALSE;

// ... (Проверки полей fio, tel, email, bdate, bio, ccheck) ...
if (empty($_POST['fio'])) {
  print('Заполните имя.<br/>');
  $errors = TRUE;
}
else{
    if (strlen($_POST['fio']) > 150) {
      print( "ФИО не должно превышать 150 символов.<br>");
      $errors = TRUE;
    }
    elseif (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s]+$/u", $_POST['fio'])) {
        print("ФИО должно содержать только буквы и пробелы.<br>");
        $errors = TRUE;
    }
}

if (empty($_POST['tel']) || !preg_match('/^\+7\d{10}$/', $_POST['tel']) ) {
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
}
else {
    foreach ($_POST['abilities'] as $ability) {
        if (!isset($abilities[$ability])) {  // Проверяем, существует ли такой ключ
            print('Выберите любимый язык программирования из списка.<br/>');
            $errors = TRUE;
            break; // Важно выйти из цикла, если нашли ошибку
        }
    }
}

if (empty($_POST['bdate']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['bdate'])) {
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
    // При наличии ошибок завершаем работу скрипта.
    exit();
}

// Сохранение в базу данных.
try {
    $stmt = $db->prepare("INSERT INTO users (fio, tel, email, gender, bdate, bio, ccheck) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['fio'], $_POST['tel'], $_POST['email'], $_POST['radio'], $_POST['bdate'], $_POST['bio'], isset($_POST["ccheck"])]);

    $a_id = $db->lastInsertId();
    $stmt = $db->prepare("INSERT INTO abilities (id, name) VALUES (?, ?)"); 
    foreach ($_POST['abilities'] as $ability) {  // Итерируемся по $_POST['abilities']
        $stmt->execute([$a_id, $ability]);
    }

} catch (PDOException $e) {
    print('Ошибка БД : ' . $e->getMessage());
    exit();
}

header('Location: ?save=1');
?>
