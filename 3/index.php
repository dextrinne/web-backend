<?php
header('Content-Type: text/html; charset=UTF-8');
echo "<link rel='stylesheet' href='style.css'>";

$user = 'u68595';
$pass = '6788124';

try {
  $db = new PDO('mysql:host=localhost;dbname=u68595', $user, $pass,
    [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
  $db->exec("SET NAMES utf8"); 
} catch (PDOException $e) {
  print('Ошибка соединения с БД: ' . $e->getMessage());
  exit();
}


$abilities = [];
try {
    $data = $db->query("SELECT user_id, lang_id FROM user")->fetchAll(PDO::FETCH_KEY_PAIR); 
    $abilities = $data;
} catch (PDOException $e) {
    print('Error: ' . $e->getMessage());
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  if (!empty($_GET['save'])) {
    print('Результаты сохранены.');
  }

  include('form.php');
  exit();
}


$errors = FALSE;

if (empty($_POST['fio']) || !preg_match('/^([A-Z]|[a-z]| |[а-я]|[А-Я]){3,150}$/ui', $_POST['fio'])) {
  print('Заполните имя.<br/>');
  $errors = TRUE;
}

if (empty($_POST['tel']) || !preg_match('/^\+?[0-9]{11,14}$/', $_POST['telephone'])) {
  print('Заполните телефон.<br/>');
  $errors = TRUE;
}

if (empty($_POST['email']) || !preg_match('/^\w{1,80}@\w{1,10}.\w{1,10}$/', $_POST['email'])) {
  print('Заполните почту.<br/>');
  $errors = TRUE;
}

if (empty($_POST['bdate']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['dateOfBirth'])) {
  print('Заполните дату рождения.<br/>');
  $errors = TRUE;
}

if (empty($_POST['radio']) || !($_POST['radio'] == "female" || $_POST['radio'] == "male")) {
  print('Выберите пол.<br/>');
  $errors = TRUE;
}

if (empty($_POST['abilities'])) {
  print('Выберите любимый язык программирования.<br/>');
  $errors = TRUE;
} else {
  foreach ($_POST['abilities'] as $ability) {
    if (!array_key_exists($ability, $abilities)) {  
      print('Выберите любимый язык программирования.<br/>');
      $errors = TRUE;
      break; 
    }
  }
}

if (empty($_POST['bio']) || !preg_match('/^(\w|\s|.|!|,|\?|\(|\)){1,1000}$/', $_POST['bio'])) {
  print('Заполните биографию.<br/>');
  $errors = TRUE;
}

if (empty($_POST['сcheck'])) {
  print('Согласие надо подписать.<br/>');
  $errors = TRUE;
}

if ($errors) {
  exit();
}

try {
  $stmt = $db->prepare("INSERT INTO users (fio, tel, email, gender, bdate, bio, ccheck) VALUES (:name, :phone, :email, :date, :sex, :bio)");
  $stmt->bindParam(':name', $_POST['fio']);
  $stmt->bindParam(':phone', $_POST['tel']);
  $stmt->bindParam(':email', $_POST['email']);
  $stmt->bindParam(':date', $_POST['bdate']);
  $stmt->bindParam(':sex', $_POST['radio']);
  $stmt->bindParam(':bio', $_POST['bio']);
  $stmt->execute();

  $id_app = $db->lastInsertId();

  $stmt = $db->prepare("INSERT INTO user (user_id, lang_id) VALUES (:id_user, :id_lang)");
  $stmt->bindParam(':id_user', $id_app);
  $stmt->bindParam(':id_lang', $ability); 

  foreach ($_POST['abilities'] as $ability) {
    $stmt->execute(); 
  }

  header('Location: ?save=1');

} catch (PDOException $e) {
  print('Ошибка сохранения в БД: ' . $e->getMessage());
  exit();
}

?>
