<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/auth.php");

if(isset($_POST["email"]) && isset($_POST["password"])) {
  $email = $_POST["email"];
  $password = $_POST["password"];
  
  try {
    $user = Auth::authenticate($email, $password);
    if($user) {
      $_SESSION['user'] = $user;
      header("Location: /panel.php");
      die();
    } else {
      $error_msg = "Niepoprawne dane logowania";
    }
  } catch (Exception $e) {
    switch($e->getCode()) {
      case AuthError::UserNotFound->value: {
        $error_msg = "Nie ma takiego użytkownika";
      } break;
    }
  }
}

$title = "Zaloguj";
require_once("templates/header.php");
?>

<main class="container">
  <?php
    if(isset($_GET['msg'])) {
      ?>
        <p><?= htmlspecialchars($_GET['msg'], ENT_QUOTES); ?></p>
      <?php
    }
  ?>
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