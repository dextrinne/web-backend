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
    $data = $db->query("SELECT lang_id, name FROM lang")->fetchAll(PDO::FETCH_KEY_PAIR); 
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

if (empty($_POST['fio']) || !preg_match('/^([A-Za-zА-Яа-я\s]){3,150}$/u', $_POST['fio'])) {
  print('Заполните имя.<br/>');
  $errors = TRUE;
}

if (empty($_POST['tel']) || !preg_match('/^\+?[0-9]{11,14}$/', $_POST['tel'])) {
  print('Заполните телефон.<br/>');
  $errors = TRUE;
}

if (empty($_POST['email']) || !preg_match('/^\w+@\w+\.\w+$/', $_POST['email'])) {
  print('Заполните почту.<br/>');
  $errors = TRUE;
}

if (empty($_POST['bdate']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['bdate'])) {
  print('Заполните дату рождения.<br/>');
  $errors = TRUE;
}

if (empty($_POST['radio']) || !in_array($_POST['radio'], ['female', 'male'])) {
  print('Выберите пол.<br/>');
  $errors = TRUE;
}

if (empty($_POST['abilities'])) {
  print('Выберите любимый язык программирования.<br/>');
  $errors = TRUE;
} else {
  foreach ($_POST['abilities'] as $ability) {
    if (!array_key_exists($ability, $abilities)) {  
      print('Недопустимый язык программирования.<br/>');
      $errors = TRUE;
      break; 
    }
  }
}

if (empty($_POST['bio']) || !preg_match('/^[\w\s.,!?"()]{1,1000}$/u', $_POST['bio'])) {
  print('Заполните биографию.<br/>');
  $errors = TRUE;
}

if (empty($_POST['сheck'])) {
  print('Согласие надо подписать.<br/>');
  $errors = TRUE;
}

if ($errors) {
  exit();
}

try {
  $stmt = $db->prepare("INSERT INTO users (fio, tel, email, bdate, gender, bio, ccheck) VALUES (:fio, :tel, :email, :bdate, :gender, :bio, :ccheck)");
  $stmt->bindParam(':fio', $_POST['fio']);
  $stmt->bindParam(':tel', $_POST['tel']);
  $stmt->bindParam(':email', $_POST['email']);
  $stmt->bindParam(':bdate', $_POST['bdate']);
  $stmt->bindParam(':gender', $_POST['radio']);
  $stmt->bindParam(':bio', $_POST['bio']);
  $stmt->bindValue(':ccheck', isset($_POST['сheck']) ? 1 : 0, PDO::PARAM_INT); 
  $stmt->execute();

  $id_app = $db->lastInsertId();

  $stmt = $db->prepare("INSERT INTO user (user_id, lang_id) VALUES (:user_id, :lang_id)");
  $stmt->bindParam(':user_id', $id_app);
  

  foreach ($_POST['abilities'] as $ability) {
    $stmt->bindParam(':lang_id', $ability);
    $stmt->execute(); 
  }

  header('Location: ?save=1');
  exit(); 

} catch (PDOException $e) {
  print('Ошибка сохранения в БД: ' . $e->getMessage());
  exit();
}

?>
