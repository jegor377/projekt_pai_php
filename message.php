<?php
require_once("templates/session.php");

if(!isset($_GET['id']) || $user['role'] !== 'sportsman') {
  header('Location: /panel.php');
  exit();
}

$message = Db::read_message($_GET['id']);

require_once("templates/header.php");
?>

<main>
  <?php if($message): ?>
    <h1>Wiadomość od trenera</h1>
    <article>
      <h2>Otrzymano</h2>
      <p><?= $message['sent_timestamp'] ?></p>
      <?php if($message['read_timestamp'] !== null): ?>
        <h2>Przeczytano</h2>
        <p><?= $message['read_timestamp'] ?></p>
      <?php endif; ?>
      <h2>Treść</h2>
      <p><?= $message['content'] ?></p>
    </article>
  <?php else: ?>
    <article>
      <p>Wiadomość nie istnieje.</p>
    </article>
  <?php endif; ?>
</main>

<?php
require_once("templates/footer.php");
?>