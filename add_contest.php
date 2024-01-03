<?php

require_once("templates/session.php");

if($user['role'] !== 'trainer') {
  header('Location: /panel.php');
  exit();
}

$contest_id = Db::add_contest($user['id']);
if($contest_id !== null) {
  header("Location: /edit_contest.php?id=$contest_id");
  exit();
}