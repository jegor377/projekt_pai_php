<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/input.php");
require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/avatar.php");

if(isset($_SESSION["user"])) {
  $user = $_SESSION['user'];
}

$css_files = [
  '/css/settings.css'
];

require_once("templates/header.php");
?>

<main>
  <article>
    <h2>Podstawowe informacje</h2>
    <form>
      <img class="avatar" src="<?= get_avatar_url($user); ?>"/>
      <div>
        <label for="avatar">Avatar</label>
        <input name="avatar" id="avatar" type="file"/>
      </div>
      <div>
        <label for="name">Imię i nazwisko</label>
        <input name="name" id="name" maxlength="256" required <?= to_val($user['name'] ?? null) ?>/>
      </div>
      <input type="submit" value="Zapisz"/>
    </form>
  </article>
  <article>
    <h2>Zmiana hasła</h2>
    <form>
      <div>
        <label for="current_password">Aktualne hasło</label>
        <input name="current_password" id="current_password" type="password" required/>
      </div>
      <div>
        <label for="new_password">Nowe hasło</label>
        <input name="new_password" id="new_password" type="password" required/>
      </div>
      <div>
        <label for="new_password_verify">Powtórz nowe hasło</label>
        <input name="new_password_verify" id="new_password_verify" type="password" required/>
      </div>
      <input type="submit" value="Zapisz"/>
    </form>
  </article>
</main>

<?php
require_once("templates/footer.php");
?>