<?php
header('Content-Type: text/html; charset=UTF-8');
echo "<link rel='stylesheet' href='style.css'>";

// Сохранение в базу данных.
$user = 'u68595'; 
$pass = '6788124'; 
$db = new PDO('mysql:host=localhost;dbname=u68595', $user, $pass,
  [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); 
  
// Вызываем языки программирования в форму
function getAbilities($db){
  try {
    $abilities = [];
    $data = $db->query("SELECT id, name FROM language")->fetchAll();
    foreach ($data as $ability) {
      $name = $ability['name'];
      $lang_id = $ability['id'];
      $abilities[$lang_id] = $name;
    }
    return $abilities;
  }
  catch(PDOException $e){
    print('Error: ' . $e->getMessage());
    exit();
  }
}

$abilities = getAbilities($db);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  $messages = array();

  // В суперглобальном массиве $_COOKIE PHP хранит все имена и значения куки текущего запроса.
  // Выдаем сообщение об успешном сохранении.
  if (!empty($_COOKIE['save'])) {
    setcookie('save', '', 100000);
    $messages[] = 'Спасибо, результаты сохранены.';
  }

  // Складываем признак ошибок в массив.
  $errors = array();
  $errors['fio'] = !empty($_COOKIE['fio_error']);
  $errors['tel'] = !empty($_COOKIE['tel_error']);
  $errors['email'] = !empty($_COOKIE['email_error']);
  $errors['abilities'] = !empty($_COOKIE['abilities_error']);
  $errors['bdate'] = !empty($_COOKIE['bdate_error']);
  $errors['radio'] = !empty($_COOKIE['radio_error']);
  $errors['bio'] = !empty($_COOKIE['bio_error']);
  $errors['ccheck'] = !empty($_COOKIE['ccheck_error']);


  // Выдаем сообщения об ошибках.
  if ($errors['fio']) {
    if($_COOKIE['fio_error']=='1'){
      $messages[] = '<div class="error">Введите ФИО.</div>';
    }
    elseif($_COOKIE['fio_error']=='2'){
      $messages[] = '<div class="error">ФИО не должно превышать 150 символов.</div>';
    }
    else{
      $messages[] = '<div class="error">ФИО должно содержать только буквы и пробелы.</div>';
    }
    setcookie('fio_error', '', 100000);
    setcookie('fio_value', '', 100000);
  }

  if ($errors['tel']) {
    setcookie('tel_error', '', 100000);
    setcookie('tel_value', '', 100000);
    $messages[] = '<div class="error">Введите корректный номер телефона.</div>';
  }
  if ($errors['email']) {
    setcookie('email_error', '', 100000);
    setcookie('email_value', '', 100000);
    $messages[] = '<div class="error">Введите корректный email.</div>';
  }
  if ($errors['abilities']) {
    setcookie('abilities_error', '', 100000);
    setcookie('abilities_value', '', 100000);
    $messages[] = '<div class="error">Выберите любимый язык программирования.</div>';
  }
  if ($errors['bdate']) {
    setcookie('bdate_error', '', 100000);
    setcookie('bdate_value', '', 100000);
    $messages[] = '<div class="error">Введите корректную дату рождения.</div>';
  }
  if ($errors['radio']) {
    setcookie('radio_error', '', 100000);
    setcookie('radio_value', '', 100000);
    $messages[] = '<div class="error">Выберите пол.</div>';
  }
  if ($errors['bio']) {
    setcookie('bio_error', '', 100000);
    setcookie('bio_value', '', 100000);
    $messages[] = '<div class="error">Заполните биографию.</div>';
  }
  if ($errors['ccheck']) {
    setcookie('ccheck_error', '', 100000);
    setcookie('ccheck_value', '', 100000);
    $messages[] = '<div class="error">Подтвердите ознакомление с контрактом.</div>';
  }

  // Складываем предыдущие значения полей в массив, если есть.
  $values = array();
  $values['fio'] = empty($_COOKIE['fio_value']) ? '' : $_COOKIE['fio_value'];
  $values['tel'] = empty($_COOKIE['tel_value']) ? '' : $_COOKIE['tel_value'];
  $values['email'] = empty($_COOKIE['email_value']) ? '' : $_COOKIE['email_value'];
  $values['abilities'] = empty($_COOKIE['abilities_value']) ? '' : $_COOKIE['abilities_value'];
  $values['bdate'] = empty($_COOKIE['bdate_value']) ? '' : $_COOKIE['bdate_value'];
  $values['radio'] = empty($_COOKIE['radio_value']) ? '' : $_COOKIE['radio_value'];
  $values['bio'] = empty($_COOKIE['bio_value']) ? '' : $_COOKIE['bio_value'];
  $values['ccheck'] = empty($_COOKIE['ccheck_value']) ? '' : $_COOKIE['ccheck_value'];
  
  include('form.php');
}

else {
  $errors = FALSE;

  // Проверяем все ошибки
  if (empty($_POST['fio'])) {
    setcookie('fio_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else {
    if (strlen($_POST['fio']) > 150) {
      setcookie('fio_error', '2', time() + 24 * 60 * 60);
      $errors = TRUE;
    } elseif (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s]+$/u", $_POST['fio'])) {
      setcookie('fio_error', '3', time() + 24 * 60 * 60);
      $errors = TRUE;
    }
  }

  if (empty($_POST['tel']) || !preg_match('/^\+7\d{10}$/', $_POST['tel']) ) {
    setcookie('tel_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  if (empty($_POST["email"]) || !filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    setcookie('email_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }

  $fav_languages = $_POST["abilities"] ?? []; // Получаем массив из формы
  if (empty($fav_languages)) {
    setcookie('abilities_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  else{
    foreach ($fav_languages as $ability) {
      if (empty($abilities[$ability])){
        setcookie('abilities_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
      }
    }
  }

  if (empty($_POST['bdate']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['bdate'])) {
    setcookie('bdate_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  if (empty($_POST['radio'])) {
    setcookie('radio_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  if (empty($_POST['bio'])) {
    setcookie('bio_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  if (empty($_POST['ccheck'])) {
    setcookie('ccheck_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }

  // Сохраняем ранее введенное в форму значение на месяц.
  setcookie('fio_value', $_POST['fio'], time() + 30 * 24 * 60 * 60);
  setcookie('tel_value', $_POST['tel'], time() + 30 * 24 * 60 * 60);
  setcookie('email_value', $_POST['email'], time() + 30 * 24 * 60 * 60);
  setcookie('abilities_value', implode(',', $fav_languages), time() + 30 * 24 * 60 * 60);
  setcookie('bdate_value', $_POST['bdate'], time() + 30 * 24 * 60 * 60);
  setcookie('radio_value', $_POST['radio'], time() + 30 * 24 * 60 * 60);
  setcookie('bio_value', $_POST['bio'], time() + 30 * 24 * 60 * 60);
  setcookie('ccheck_value', $_POST['ccheck'], time() + 30 * 24 * 60 * 60);



  if ($errors) {
    // При наличии ошибок перезагружаем страницу и завершаем работу скрипта.
    header('Location: index.php');
    exit();
  }
  else {
    // Удаляем Cookies с признаками ошибок.
    setcookie('fio_error', '', 100000);
    setcookie('tel_error', '', 100000);
    setcookie('email_error', '', 100000);
    setcookie('abilities_error', '', 100000);
    setcookie('bdate_error', '', 100000);
    setcookie('radio_error', '', 100000);
    setcookie('bio_error', '', 100000);
    setcookie('ccheck_error', '', 100000);
  }

  // Сохранение в БД.
  try {
    $stmt = $db->prepare("INSERT INTO user (fio, tel, email, gender, bdate, bio, ccheck) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['fio'], $_POST['tel'], $_POST['email'], $_POST['radio'], $_POST['bdate'], $_POST['bio'], isset($_POST["ccheck"])]);
  
    $a_id = $db->lastInsertId();
  
    $stmt = $db->prepare("INSERT INTO user_language (user_id , lang_id ) VALUES (?, ?)");
    foreach ($_POST['abilities'] as $ability) {
        $stmt->execute([$a_id, $ability]);
    }
  
  } catch (PDOException $e) {
    print('Ошибка БД : ' . $e->getMessage());
    exit();
  }

  // Сохраняем куку с признаком успешного сохранения.
  setcookie('save', '1');
  header('Location: index.php');
}
