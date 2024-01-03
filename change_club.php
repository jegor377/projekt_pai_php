<?php

require_once("templates/session.php");

if(!isset($_POST['club_id'])) {
  header("Location: /panel.php?msg=Nie podano id klubu!");
  exit();
}

$res = Db::change_club($user['id'], $_POST['club_id']);
if(!$res) {
  header("Location: /panel.php?msg=Nie udało się zaktualizować wybranego klubu!");
  exit();
}

header("Location: /panel.php?msg=Zaktualizowano wybrany klub pomyślnie!");
exit();