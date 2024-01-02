<?php
session_start();

define("SHORTCUT_LEN", 20);

require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/db.php");
require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/string.php");
require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/avatar.php");

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

$trainer_id = $user['role'] === 'trainer' ? $user['id'] : $user['trainer_id'];
if($trainer_id !== null) {
  $contests = Db::get_contests_by_trainer_id($trainer_id);
}

$messages = Db::get_messages_to_user_id($user['id']);

if($user['role'] === 'trainer') {
  $students = Db::get_trainer_students($user['id']);
}

function Contest($contest, $user) {
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
      <?php if($user['role'] === 'trainer'): ?> 
        <a href="/edit_contest.php?id=<?= $contest['id'] ?>">Edytuj</a>
        <a href="#">Raport</a>
      <?php endif; ?>
    </div>
  </div>
  <?php
}

function Message($message) {
  ?>
  <div class="message">
    <p>Wysłano: <?= $message['sent_timestamp'] ?></p>
    <p><?= $message['content'] ?></p>
  </div>
  <?php
}
?>

<main class="container <?= $user['role'] === 'trainer' ? 'trainer-container' : 'sportsman-container' ?>">
  <article class="club" id="club">
    <h2>Klub</h2>
    <?php if(isset($user_club) && $user_club): ?>
      <p><span>Nazwa:</span> <?= $user_club['name'] ?></p>
      <p>
        <span>Adres:</span> <?= $user_club['address'] ?>
        <br>
        <?= $user_club['city'] ?>, <?= $user_club['postcode'] ?>
      </p>
    <?php else: ?>
      <p>Nie jesteś przypisany do żadnego klubu</p>
    <?php endif; ?>
    <a href="#">Zmień</a>
  </article>

  <?php if(isset($contests) && $contests): ?>
    <article class="contests" id="future_contests">
      <h2>Nadchodzące zawody</h2>
      <div class="contests-container">
        <?php
          foreach($contests as $contest) {
            if(!$contest['finished']) Contest($contest, $user);
          }
        ?>
      </div>
      <?php if($user['role'] === 'trainer'): ?>
        <a href="#">Dodaj</a>
      <?php endif; ?>
    </article>
    <article class="contests" id="finished_contests">
      <h2>Zakończone zawody</h2>
      <div class="contests-container">
        <?php
          foreach($contests as $contest) {
            if($contest['finished']) Contest($contest, $user);
          }
        ?>
      </div>
    </article>
  <?php endif; ?>
  <?php if($user['role'] === 'sportsman'): ?>
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
  <?php endif; ?>
  <?php if($user['role'] === 'trainer'): ?>
    <article id="messages" class="trainer-messages">
      <h2>Wiadomość do wszystkich sportowców</h2>
      <form class="message-to-all">
        <textarea name="message"></textarea>
        <input type="submit" value="Wyślij"/>
      </form>
    </article>
    <article id="students">
      <h2>Sportowcy</h2>
      <div class="students-container">
        <?php foreach($students as $student): ?>
          <div class="student">
            <div class="student-head">
              <img class="student-avatar" src="<?= get_avatar_url($student); ?>"/>
              <p><?= $student['name'] ?></p>
            </div>
            <div class="student-actions">
              <a href="#">Wyniki</a>
              <a href="#">Wiadomości</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </article>
  <?php endif; ?>
</main>

<script src="/js/panel.js"></script>

<?php
require_once("templates/footer.php");
?>