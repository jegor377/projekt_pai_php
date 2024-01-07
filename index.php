<?php

if(isset($_SESSION["user_id"])) {
  header("Location: /panel.php");
  die();
} else {
  header("Location: /login.php");
  die();
}
