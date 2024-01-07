<?php

require_once("templates/session.php");

if(!isset($_GET['id']) || $user['role'] !== 'trainer') {
  header('Location: /panel.php');
  exit();
}

if(isset($_POST['receiver_id']) && isset($_POST['content']) && $_POST['content'] !== '') {
  if (!Db::post_message($_POST["receiver_id"], $user["id"], $_POST["content"])) {
    $err_message = "Nie udało się wysłać wiadomości!";
  }
}

$id = $_GET['id'];
$sportsman = Db::get_user_by_id($id);
if(!$sportsman) {
  header('Location: /panel.php?msg=Użytkownik nie istnieje');
  exit();
}

$messages = Db::get_messages_to_user_id($id);

$css_files = [
  '/css/messages.css'
];

require_once("templates/header.php");
?>

<main class="container">
  <h1>Ostatnie wiadomości do sportowca: <?= $sportsman['name'] ?></h1>
  <article class="messages">
    <?php foreach($messages as $message): ?>
      <div class="message">
        <p class="message-timestamp"><?= $message['sent_timestamp'] ?></p>
        <p class="message-content"><?= str_replace("\n", "<br>", $message['content']) ?></p>
        <p class="message-state <?= $message['read_timestamp'] !== null ? "message-arrived" : "message-pending" ?>"><?= $message['read_timestamp'] === null ? 'Dostarczono' : 'Przeczytano: ' . $message['read_timestamp'] ?></p>
      </div>
    <?php endforeach; ?>
  </article>
  <article class="write-message">
    <form action="/messages.php?id=<?= $id ?>" method="POST">
      <input type="hidden" name="receiver_id" value="<?= $sportsman['id'] ?>"/>
      <textarea name="content" minlength="1" placeholder="Napisz wiadomość"></textarea>
      <input type="submit" value="Wyślij"/>
    </form>
    <?php if(isset($err_message)): ?>
      <p><?= $err_message ?></p>
    <?php endif; ?>
  </article>
</main>

<?php
require_once("templates/footer.php");
?>