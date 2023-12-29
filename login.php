<?php
require_once("lib/header.php");
require_once("lib/auth.php");

if(isset($_POST["email"]) && isset($_POST["password"])) {
  $email = $_POST["email"];
  $password = $_POST["password"];
  
  $user = Auth::authenticate($email, $password);
  if($user) {
    $_SESSION['user'] = "abc";
  } else {
    $error_msg = "Niepoprawne dane logowania";
  }
}

$title = "Zaloguj";
require_once("templates/header.php");
?>

<main class="container">
  <form action="/login.php" method="POST">
    <div>
      <label for="email">Email</label>
      <input id="email" name="email" type="email" required maxlength="100"/>
    </div>
    <div>
      <label for="password">Hasło</label>
      <input id="password" name="password" type="password" required/>
    </div>
    <input type="submit" value="Zaloguj"/>
  </form>
  <?php
    if(isset($error_msg)) {
      ?>
      <p><?= $error_msg ?></p>
      <?php
    }
  ?>
</main>

<?php
require_once("templates/footer.php");