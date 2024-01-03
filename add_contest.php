<?php

session_start();

require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/db.php");

if(isset($_SESSION["user_id"])) {
  $user = Db::get_user_by_id($_SESSION['user_id']);
} else {
  header("Location: /login.php");
  exit();
}

if($user['role'] !== 'trainer') {
  header('Location: /panel.php');
  exit();
}

$contest_id = Db::add_contest($user['id']);
if($contest_id !== null) {
  header("Location: /edit_contest.php?id=$contest_id");
  exit();
}