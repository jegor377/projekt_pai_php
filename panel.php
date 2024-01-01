<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/db.php");

if(isset($_SESSION["user_id"])) {
  $user = Db::get_user_by_id($_SESSION['user_id']);
} else {
  header("Location: /login.php");
  exit();
}

$css_files = [
  '/css/panel.css'
];

require_once("templates/header.php");

if($user['club_id'] !== null) {
  $user_club = Db::get_club_by_id($user['club_id']);
}

if($user['trainer_id'] !== null) {
  $contests = Db::get_contests_by_trainer_id($user['trainer_id']);
}
?>

<main class="container">
  <!-- SPORTOWIEC -->
  <?php if(isset($user_club) && $user_club): ?>
    <article class="club">
      <p><?= $user_club['name'] ?></p>
    </article>
  <?php endif; ?>
  <?php if(isset($contests) && $contests): ?>
    <article class="contests">
      <h2>Nadchodzące zawody</h2>
      <div class="contests-container">
        <?php foreach($contests as $contest): ?>
          <?php if(!$contest['finished']): ?>
            <a href="#"><?= $contest['time'] ?></a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </article>
    <article class="contests">
      <h2>Zakończone zawody</h2>
      <div class="contests-container">
        <?php foreach($contests as $contest): ?>
          <?php if($contest['finished']): ?>
            <a href="#"><?= $contest['time'] ?></a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </article>
  <?php endif; ?>
  <article>
    <h2>Wiadomości</h2>
    <div>
    </div>
  </article>
</main>

<?php
require_once("templates/footer.php");
?>