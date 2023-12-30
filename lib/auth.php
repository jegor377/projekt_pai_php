<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/db.php");

enum AuthError: int {
  case UserNotFound = 0;
}

class Auth {
  public static function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
  }
  
  public static function verify_password($password, $hash) {
    return password_verify($password, $hash);
  }
  
  public static function authenticate($email, $password) {
    $user = Db::get_user($email);
    if($user === null) throw new Exception("User not found", AuthError::UserNotFound->value);
    if(password_verify($password, $user['password_hash'])) {
      return $user;
    }
    return null;
  }
}