<?php
  require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/avatar.php");

  if(isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
  }
?>

<header class="navbar">
  <a href="/" class="navbar-brand">Gymnousia</a>
  <nav class="navbar-options">
    <?php if(isset($user)): ?>
      <div class="user-btn">
        <img src="<?= get_avatar_url($user); ?>"/>
        <span>Witaj, <?= $user['name'] ?></span>
      </div>
      <a href="panel.php" class="navbar-option">Panel</a>
      <a href="settings.php" class="navbar-option">Ustawienia</a>
      <a href="#" class="navbar-option" id="logout_btn">Wyloguj</a>
    <?php else: ?>
      <a href="login.php" class="navbar-option">Zaloguj</a>
      <a href="register.php" class="navbar-option">Zarejestruj się</a>
    <?php endif; ?>
  </nav>
</header>