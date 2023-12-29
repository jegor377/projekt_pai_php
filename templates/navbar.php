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
        <a href="#" class="navbar-option">Wyloguj</a>
        <?php
      } else {
        ?>
        <a href="login.php" class="navbar-option">Zaloguj</a>
        <?php
      }
    ?>
  </nav>
</header>