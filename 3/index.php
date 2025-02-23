<?php
header('Content-Type: text/html; charset=UTF-8');
echo "<link rel='stylesheet' href='style.css'>";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

  if (!empty($_GET['submit'])) {
    print('Спасибо, результаты сохранены.');
  }
  // Включаем содержимое файла form.php.
  include('form.php');
  exit();
}

// Проверяем на наличие ошибок.
$errors = FALSE;

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

if (empty($P['abilities'])) {
  print('Выберите любимый язык программирования.<br/>');
  $errors = TRUE;
}
else{
  foreach ($_POST['abilities'] as $ability) {
    if (empty($abilities[$ability])){
      print('Выберите любимый язык программирования.<br/>');
      $errors = TRUE;
    }
  }
}


if (empty($_POST['bdate']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['bdate'])) {
    print("Введите корректную дату рождения.<br>");
    $errors = TRUE;
}


if (empty($P['radio']) || !($P['radio'] == "female" || $P['radio'] == "male")) {
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
$user = 'u68595'; 
$pass = '6788124'; 
$db = new PDO('mysql:host=localhost;dbname=u68595', $user, $pass,
  [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); 


// Языки программирования
try {
  $stmt = $db->prepare("INSERT INTO users (fio, tel, email, radio, bdate, bio, ccheck) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([$_POST['fio'], $_POST['tel'], $_POST['email'], $_POST['radio'], $_POST['bdate'], $_POST['bio'], isset($_POST["ccheck"]) ? 1 : 0]);
} catch (PDOException $e) {
  print('Ошибка БД: ' . $e->getMessage());
  exit();
}

$user_id = $db->lastInsertId();
try{
  $stmt = $db->prepare("SELECT id FROM abilities WHERE name = ?");
  $insert_stmt = $db->prepare("INSERT INTO user (user_id, lang_id) VALUES (?, ?)");
  
  foreach ($ability as $language) {
      // Получаем ID языка программирования
      $stmt->execute([$language]);
      $lang_id = $stmt->fetchColumn();
      
      if ($lang_id) {
          // Связываем пользователя с языком
          $insert_stmt->execute([$user_id, $lang_id]);
      }
  }
}
catch (PDOException $e) {
  print('Ошибка БД: ' . $e->getMessage());
  exit();
}

header('Location: ?save=1');
