<div id="form"><h2>Форма</h2></div>
<form action="" method="POST">
    ФИО:<br>
    <input name="fio" type="text" placeholder="ФИО"><br>
    
    Телефон:<br>
    <input name="tel" type="tel" placeholder="Номер телефона"><br>
    
    Почта:<br>
    <input name="email" type="email" placeholder="Email"><br>

    Дата рождения:<br>
    <input name="bdate" type="date"><br>

    Пол:<br>
    <label><input class="radio" name="radio" type="radio" value="Female">Женский</label>
    <label><input class="radio" name="radio" type="radio" value="Male">Мужской</label><br> 
    
    Любимый язык программирования:
    <br>
    <select id="abilities" name="abilities[]" multiple="multiple">
      <?php 
      foreach ($abilities as $key => $value) {
        printf('<option value="%s">%s</option>', $key, $value);
      } 
      ?>
    </select>
    <br>

    Биография:<br>
    <textarea id="bio" name="bio"></textarea><br>

    <input id="ccheck" name="ccheck" type="checkbox" checked="checked">С контрактом ознакомлен(а)<br>
    <input id="submit" name="submit" type="submit" value="Сохранить">
</form>
