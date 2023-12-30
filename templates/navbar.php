<?php
  if(isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
  }
?>

<header class="navbar">
  <a href="/" class="navbar-brand">Gymnousia</a>
  <nav class="navbar-options">
    <?php
      if(isset($user)) {
        ?>
        <span>Witaj <?= $user['name'] ?></span>
        <a href="#" class="navbar-option" id="logout_btn">Wyloguj</a>
        <?php
      } else {
        ?>
        <a href="login.php" class="navbar-option">Zaloguj</a>
        <a href="register.php" class="navbar-option">Zarejestruj się</a>
        <?php
      }
    ?>
  </nav>
</header>