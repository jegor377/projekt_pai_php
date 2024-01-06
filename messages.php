<?php

require_once("templates/session.php");

if(!isset($_GET['id']) || $user['role'] !== 'trainer') {
  header('Location: /panel.php');
  exit();
}

$id = $_GET['id'];
$sportsman = Db::get_user_by_id($id);
if(!$sportsman) {
  header('Location: /panel.php?msg=Użytkownik nie istnieje');
  exit();
}

$messages = Db::get_messages_to_user_id($id);

require_once("templates/header.php");
?>

<main>
  <h1>Wiadomości sportowca: <?= $sportsman['name'] ?></h1>
  <article>
    <?php foreach($messages as $message): ?>
      <div>
        <?= $message['content'] ?>
      </div>
    <?php endforeach; ?>
  </article>
</main>

<?php
require_once("templates/footer.php");
?>