<?php
session_start();

define("SHORTCUT_LEN", 20);

require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/db.php");
require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/string.php");

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

$messages = Db::get_messages_to_user_id($user['id']);

function UserContest($contest, $user) {
  ?>
  <div class="contest">
    <div class="contest-head">
      <p><?= $contest['time'] ?></p>
      <p><?= shortcut($contest['description'], SHORTCUT_LEN) ?>
    </div>
    <div class="contest-actions">
      <?php if($contest['finished'] && $user['role'] === 'sportsman'): ?> 
        <a href="#">Sprawdź wyniki</a>
      <?php endif; ?>
    </div>
  </div>
  <?php
}

function Message($message) {
  ?>
  <div>
    <p>Wysłano: <?= $message['sent_timestamp'] ?></p>
    <p><?= $message['content'] ?></p>
  </div>
  <?php
}
?>

<main class="container">
  <!-- SPORTOWIEC -->
  <?php if(isset($user_club) && $user_club): ?>
    <article class="club" id="club">
      <p><span>Nazwa:</span> <?= $user_club['name'] ?></p>
      <p>
        <span>Adres:</span> <?= $user_club['address'] ?>
        <br>
        <?= $user_club['city'] ?>, <?= $user_club['postcode'] ?>
      </p>
      <a href="#">Zmień</a>
    </article>
  <?php endif; ?>
  <?php if(isset($contests) && $contests): ?>
    
    <article class="contests" id="future_contests">
      <h2>Nadchodzące zawody</h2>
      <div class="contests-container">
        <?php
          foreach($contests as $contest) {
            if(!$contest['finished']) UserContest($contest, $user);
          }
        ?>
      </div>
    </article>
    <article class="contests" id="finished_contests">
      <h2>Zakończone zawody</h2>
      <div class="contests-container">
        <?php
          foreach($contests as $contest) {
            if($contest['finished']) UserContest($contest, $user);
          }
        ?>
      </div>
    </article>
  <?php endif; ?>
  <article id="messages">
    <h2>Wiadomości od trenera z ostatniego tygodnia</h2>
    <div class="messages-container">
      <?php
        foreach($messages as $message) {
          if($user['trainer_id'] == null || ($message['sender_id'] == $user['trainer_id'])) {
            Message($message);
          }
        }
      ?>
    </div>
  </article>
</main>

<script src="/js/panel.js"></script>

<?php
require_once("templates/footer.php");
?>