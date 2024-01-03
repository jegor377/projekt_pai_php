<?php

session_start();

require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/db.php");

if(isset($_SESSION["user_id"])) {
  $user = Db::get_user_by_id($_SESSION['user_id']);
} else {
  header("Location: /login.php");
  exit();
}