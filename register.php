<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/db.php");

$trainers = Db::get_all_trainers();

require_once("templates/header.php");
?>

<main>
  <form id="register_form">
    <div>
      <label>Imię i nazwisko</label>
      <input name="name" id="name" type="name" required maxlength="256"/>
    </div>
    <div>
      <label>Email</label>
      <input name="email" id="email" type="email" required maxlength="100"/>
    </div>
    <div>
      <label>Hasło</label>
      <input name="password" id="password" type="password" required/>
    </div>
    <div>
      <label>Powtórz hasło</label>
      <input name="password_verify" id="password_verify" type="password" required/>
    </div>

    <div>
      <select name="role" id="role">
        <?php if($trainers->rowCount() > 0): ?>
          <option value="sportsman" selected>Sportowiec</option>
        <?php endif; ?>
        <option value="trainer" <?= $trainers->rowCount() === 0 ? "selected" : "" ?>>Trener</option>
      </select>
    </div>

    <?php if($trainers->rowCount() > 0): ?>
      <div id="trainer_id">
        <select name="trainer_id">
          <?php foreach($trainers as $row): ?>
            <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>

    <input type="submit" value="Zarejestruj"/>
  </form>
</main>

<script src="/js/register.js"></script>
<?php
require_once("templates/footer.php");