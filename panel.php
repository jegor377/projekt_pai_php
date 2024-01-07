<?php

require_once("templates/session.php");

if(isset($_POST["message"]) && isset($_POST["receiver_id"]) && $_POST["message"] !== "" && $user['role'] === 'trainer') {
  if (!Db::post_message($_POST["receiver_id"], $user["id"], $_POST["message"])) {
    $send_msg = "Nie udało się wysłać wiadomości!";
  } else {
    $send_msg = "Wiadomość została wysłana do wszystkich przypisanych sportowców!";
  }
}

define("SHORTCUT_LEN", 20);

require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/string.php");
require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/avatar.php");

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

if($user['role'] === 'sportsman') {
  $messages = Db::get_messages_to_user_id($user['id'], 100, true);
}

if($user['role'] === 'trainer') {
  $students = Db::get_trainer_students($user['id']);
}

$clubs = Db::get_available_clubs();

function Contest($contest, $user) {
  ?>
  <div class="contest">
    <div class="contest-head">
      <p><?= $contest['time'] ?></p>
      <p><?= shortcut($contest['description'], SHORTCUT_LEN) ?>
    </div>
    <div class="contest-actions">
      <?php if($contest['finished'] && $user['role'] === 'sportsman'): ?> 
        <a href="/student_results.php?id=<?= $contest['id'] ?>">Sprawdź wyniki</a>
      <?php endif; ?>
      <?php if($user['role'] === 'trainer'): ?> 
        <a href="/edit_contest.php?id=<?= $contest['id'] ?>">Edytuj</a>
        <a href="/raport.php?id=<?= $contest['id'] ?>">Raport</a>
      <?php endif; ?>
    </div>
  </div>
  <?php
}

function Message($message) {
  ?>
    <a href="/message.php?id=<?= $message['id'] ?>" class="message">
      <p>Wysłano: <?= $message['sent_timestamp'] ?></p>
      <p><?= shortcut($message['content'], 10) ?></p>
    </a>
  <?php
}

?>

<main class="container <?= $user['role'] === 'trainer' ? 'trainer-container' : 'sportsman-container' ?>">
  <article class="info">
    <p><?= $_GET['msg'] ?? '' ?></p>
  </article>
  <article class="club box" id="club">
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

    <?php
      $clubs = array_filter($clubs, function($club) {
        global $user;
        return $club['id'] !== $user['club_id'];
      });
    ?>
    <?php if($clubs): ?>
      <form action="/change_club.php" method="POST">
        <select name="club_id">
          <?php foreach($clubs as $club): ?>
            <option value="<?= $club['id'] ?>"><?= $club['name'] ?></option>
          <?php endforeach; ?>
        </select>
        <input type="submit" value="Zmień"/>
      </form>
    <?php endif; ?>
  </article>

  <?php if(isset($contests) && $contests): ?>
    <article class="contests box" id="future_contests">
      <h2>Nadchodzące zawody</h2>
      <div class="contests-container">
        <?php
          foreach($contests as $contest) {
            if(!$contest['finished']) Contest($contest, $user);
          }
        ?>
      </div>
      <br>
      <?php if($user['role'] === 'trainer'): ?>
        <a href="/add_contest.php">Dodaj</a>
      <?php endif; ?>
    </article>
    <article class="contests box" id="finished_contests">
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
    <article id="messages" class="box">
      <h2>Wiadomości od trenera z ostatniego tygodnia</h2>
      <div class="messages-container">
        <?php
          if($messages) {
            foreach($messages as $message) {
              if($user['trainer_id'] == null || ($message['sender_id'] == $user['trainer_id'])) {
                Message($message);
              }
            }
          } else {
            ?>
              <p>Brak wiadomości</p>
            <?php
          }
        ?>
      </div>
    </article>
  <?php endif; ?>
  <?php if($user['role'] === 'trainer'): ?>
    <article id="messages" class="trainer-messages box">
      <h2>Wiadomość do wszystkich sportowców</h2>
      <form class="message-to-all" action="/panel.php" method="POST">
        <input type="hidden" name="receiver_id" value="all"/>
        <textarea name="message" placeholder="Napisz wiadomość"></textarea>
        <input type="submit" value="Wyślij"/>
      </form>
      <?php if(isset($send_msg)): ?>
        <p><?= $send_msg ?></p>
      <?php endif; ?>
    </article>
    <article id="students" class="box">
      <h2>Sportowcy</h2>
      <div class="students-container">
        <?php foreach($students as $student): ?>
          <div class="student">
            <div class="student-head">
              <img class="student-avatar" src="<?= get_avatar_url($student); ?>"/>
              <p><?= $student['name'] ?></p>
            </div>
            <div class="student-actions">
              <a href="/sportsman_results.php?id=<?= $student['id'] ?>">Wyniki</a>
              <a href="/messages.php?id=<?= $student['id'] ?>">Wiadomości</a>
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