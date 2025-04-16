<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>⋆｡˚ ☁️ ˚｡⋆</title>
</head>
<body>

  <div class="error_messages" <?php if (empty($messages)) {
      print 'display="none"';
  } else {
      print 'display="block"';
  } ?>>
      <?php
      if (!empty($messages)) {
          print('<div id="messages">');
          foreach ($messages as $message) {
              print($message);
          }
          print('</div>');
      }
      ?>
  </div>


<div id="form"><h2>Форма</h2></div>
<form action="" method="POST">
    ФИО:<br>
    <input name="fio" type="text" placeholder="ФИО"<?php if ($errors['fio']) {
        print 'class="error"';
    } ?> value="<?php print htmlspecialchars($values['fio'] ?? ''); ?>" /><br>

    Телефон:<br>
    <input name="tel" type="tel" placeholder="Номер телефона" <?php if ($errors['tel']) {
        print 'class="error"';
    } ?> value="<?php print htmlspecialchars($values['tel'] ?? ''); ?>" /><br>
    <h3> *используйте телефонный код +7</h3>

    Почта:<br>
    <input name="email" type="email" placeholder="Email" <?php if ($errors['email']) {
        print 'class="error"';
    } ?> value="<?php print htmlspecialchars($values['email'] ?? ''); ?>" /><br>

    Дата рождения:<br>
    <input name="bdate" type="date" <?php if ($errors['bdate']) {
        print 'class="error"';
    } ?> value="<?php print htmlspecialchars($values['bdate'] ?? ''); ?>" /><br>

    Пол:<br>
    <div class="radio-group <?php if (!empty($errors['radio'])) {
        echo 'radio-error';
    } ?>">
        <label>
            <input class="radio" name="radio" type="radio" value="Female" <?php if (isset($values['radio']) && $values['radio'] == 'Female') {
                print 'checked="checked"';
            } ?>/>Женский
        </label>
        <label>
            <input class="radio" name="radio" type="radio" value="Male" <?php if (isset($values['radio']) && $values['radio'] == 'Male') {
                print 'checked="checked"';
            } ?>/>Мужской
        </label>
    </div>

    Любимый язык программирования:
    <br>
    <select id="abilities" name="abilities[]" multiple="multiple" <?php if ($errors['abilities']) {
        print 'class="error"';
    } ?>>
        <?php
        // Преобразуем строку abilities в массив, если она не пустая и не массив
        if (isset($values['abilities']) && !is_array($values['abilities'])) {
            $selected_abilities = explode(',', $values['abilities']);
        } else {
            $selected_abilities = $values['abilities'] ?? []; // Используем пустой массив, если $values['abilities'] не задано
        }

        foreach ($abilities as $key => $value) {
            $selected = in_array($key, $selected_abilities) ? 'selected="selected"' : '';
            printf('<option value="%s" %s>%s</option>', $key, $selected, htmlspecialchars($value));
        }
        ?>
    </select>
    <br>

    Биография:<br>
    <textarea id="bio" name="bio" <?php if ($errors['bio']) {
        print 'class="error"';
    } ?>><?php print htmlspecialchars($values['bio'] ?? ''); ?></textarea><br>

    <input id="ccheck" name="ccheck" type="checkbox" value="1" <?php if ($errors['ccheck'] || (isset($values['ccheck']) && $values['ccheck'] == 1)) {
        print 'checked="checked"';
    } ?>/> С контрактом ознакомлен(а)<br>
    <input id="submit" name="submit" type="submit" value="Сохранить"><br>
    <a href="login.php">Уже есть аккаунт? Войдите здесь</a>
</form>

</body>
</html>
