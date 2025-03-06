<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⋆｡˚ ☁️ ˚｡⋆</title>
  </head>
  <body>
    <div id="form"><h2>Форма</h2></div>
    <form action="" method="POST">
      ФИО:<br>
      <input name="fio" type="text" placeholder="ФИО" <?php if (!empty($errors['fio'])) {print 'class="error"';} ?> value="<?php print isset($values['fio']) ? htmlspecialchars($values['fio']) : ''; ?>" /><br>
      <?php if (!empty($errors['fio'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($errors['fio']); ?></div>
      <?php endif; ?>
        
      Телефон:<br>
      <input name="tel" type="tel" placeholder="Номер телефона" <?php if (!empty($errors['tel'])) {print 'class="error"';} ?> value="<?php print isset($values['tel']) ? htmlspecialchars($values['tel']) : ''; ?>" /><br>
      <?php if (!empty($errors['tel'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($errors['tel']); ?></div>
      <?php endif; ?>
        
      Почта:<br>
      <input name="email" type="email" placeholder="Email" <?php if (!empty($errors['email'])) {print 'class="error"';} ?> value="<?php print isset($values['email']) ? htmlspecialchars($values['email']) : ''; ?>" /><br>
      <?php if (!empty($errors['email'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($errors['email']); ?></div>
      <?php endif; ?>

      Дата рождения:<br>
      <input name="bdate" type="date" <?php if (!empty($errors['bdate'])) {print 'class="error"';} ?> value="<?php print isset($values['bdate']) ? htmlspecialchars($values['bdate']) : ''; ?>" /><br>
      <?php if (!empty($errors['bdate'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($errors['bdate']); ?></div>
      <?php endif; ?>

      Пол:<br>
      <label><input class="radio" name="radio" type="radio" value="Female" <?php if (!empty($errors['radio'])) {print 'class="error"';} ?>  <?php if (isset($values['radio']) && $values['radio'] == 'Female') print 'checked="checked"'; ?>/>Женский</label>
      <label><input class="radio" name="radio" type="radio" value="Male" <?php if (!empty($errors['radio'])) {print 'class="error"';} ?>  <?php if (isset($values['radio']) && $values['radio'] == 'Male') print 'checked="checked"'; ?>/>Мужской</label><br>
      <?php if (!empty($errors['radio'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($errors['radio']); ?></div>
      <?php endif; ?>
        
      Любимый язык программирования:
      <br>
      <select id="abilities" name="abilities[]" multiple="multiple" <?php if (!empty($errors['abilities'])) {print 'class="error"';} ?>>
        <?php 
        foreach ($abilities as $key => $value) {
          printf('<option value="%s" %s>%s</option>', $key, (isset($values['abilities']) && in_array($key, $values['abilities'])) ? 'selected="selected"' : '', $value);
        } 
        ?>
      </select>
      <br>
      <?php if (!empty($errors['abilities'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($errors['abilities']); ?></div>
      <?php endif; ?>

      Биография:<br>
      <textarea id="bio" name="bio" <?php if (!empty($errors['bio'])) {print 'class="error"';} ?>><?php print isset($values['bio']) ? htmlspecialchars($values['bio']) : ''; ?></textarea><br>
      <?php if (!empty($errors['bio'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($errors['bio']); ?></div>
      <?php endif; ?>

      <input id="ccheck" name="ccheck" type="checkbox" <?php if (!empty($errors['ccheck'])) {print 'class="error"';} ?>  <?php if (isset($_POST['ccheck']) || !isset($errors['ccheck'])) {print 'checked="checked"';} ?>/> С контрактом ознакомлен(а)<br>
       <?php if (!empty($errors['ccheck'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($errors['ccheck']); ?></div>
      <?php endif; ?>
      <input id="submit" name="submit" type="submit" value="Сохранить">
    </form>
    
  </body>
</html>
