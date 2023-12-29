<?php
require_once("db.php");

class Auth {
  public static function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
  }
  
  public static function verify_password($password, $hash) {
    return password_verify($password, $hash);
  }
  
  public static function authenticate($email, $password) {
    $db = new Db();
    $db->init();
    $user = $db->get_user($email);
    if(password_verify($password, $user['password_hash'])) {
      return $user;
    }
    return null;
  }
}