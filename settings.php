<?php
session_start();
require_once("templates/header.php");
?>

<main>
  <article>
    <h2>Podstawowe informacje</h2>
    <form>
      <div></div>
    </form>
  </article>
  <article>
    <h2>Zmiana hasła</h2>
    <form>
      <div>
        <label>Aktualne hasło</label>
        <input name="current_password" id="current_password" type="password" required/>
      </div>
      <div>
        <label>Nowe hasło</label>
        <input name="new_password" id="new_password" type="password" required/>
      </div>
      <div>
        <label>Powtórz nowe hasło</label>
        <input name="new_password_verify" id="new_password_verify" type="password" required/>
      </div>
    </form>
  </article>
</main>

<?php
require_once("templates/footer.php");
?>