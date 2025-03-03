<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⋆｡˚ ☁︎ ˚｡⋆</title>
  </head>
  <body>

    <div class="error_messages" <?php if (empty($messages)) {print 'display="none"';} else {print 'display="block"';} ?>>
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
      <input name="fio" type="text" placeholder="ФИО"<?php if ($errors['fio']) {print 'class="error"';} ?> value="<?php print $values['fio']; ?>" /><br>
        
      Телефон:<br>
      <input name="tel" type="tel" placeholder="Номер телефона" <?php if ($errors['tel']) {print 'class="error"';} ?> value="<?php print $values['tel']; ?>" /><br>
        
      Почта:<br>
      <input name="email" type="email" placeholder="Email" <?php if ($errors['email']) {print 'class="error"';} ?> value="<?php print $values['email']; ?>" /><br>

      Дата рождения:<br>
      <input name="bdate" type="date" <?php if ($errors['bdate']) {print 'class="error"';} ?> value="<?php print $values['bdate']; ?>" /><br>

      Пол:<br>
      <label><input class="radio" name="radio" type="radio" value="Female" <?php if ($errors['radio']) {print 'class="error"';} ?> value="<?php print $values['radio']; ?>" />Женский</label>
      <label><input class="radio" name="radio" type="radio" value="Male" <?php if ($errors['radio']) {print 'class="error"';} ?> value="<?php print $values['radio']; ?>" />Мужской</label><br> 
        
      Любимый язык программирования:
      <br>
      <select id="abilities" name="abilities[]" multiple="multiple" <?php if ($errors['abilities']) {print 'class="error"';} ?> value="<?php print $values['abilities']; ?>">
        <?php 
        foreach ($abilities as $key => $value) {
          printf('<option value="%s">%s</option>', $key, $value);
        } 
        ?>
      </select>
      <br>

      Биография:<br>
      <textarea id="bio" name="bio" <?php if ($errors['bio']) {print 'class="error"';} ?>><?php print $values['bio']; ?></textarea><br>

      <input id="ccheck" name="ccheck" type="checkbox" <?php if ($errors['ccheck']) {print 'class="error"';} ?>  <?php if (!$errors['ccheck']) {print 'checked="checked"';} ?>/> С контрактом ознакомлен(а)<br>
      <input id="submit" name="submit" type="submit" value="Сохранить">
    </form>
    
  </body>
</html>




