<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/config.php");
define("MAX_EMAIL_LEN", 100);

enum RegisterError: int {
  case EmailMissing = 0;
  case EmailTooLong = 1;
  case EmailFormatIncorrect = 2;
  case PasswordMissing = 3;
  case PasswordVerifyMissing = 4;
  case RoleMissing = 5;
  case RoleIncorrect = 6;
  case TrainerIdMissing = 7;
  case PasswordsDontMatch = 8;
  case NameMissing = 9;
  case UserExists = 10;
  case NameTooLong = 11;
  case Other = 12;
}

class RegisterException extends Exception {
  public function __construct($message = "", $code = 0) {
    parent::__construct($message, $code);
  }
}

class Db {
  private static $dbh = null;
  
  public static function init() {
    if (self::$dbh == null) {
      self::$dbh = new PDO("mysql:host=localhost;dbname=" . Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
    }
  }

  public static function get_user($email) {
    $sth = self::$dbh->prepare("SELECT * FROM users WHERE email = :email");
    $sth->bindParam(":email", $email, PDO::PARAM_STR);
    $sth->execute();
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    if($row) {
      return $row;
    }
    return null;
  }

  public static function register_user($user) {
    $email = $user["email"] ?? throw new RegisterException("Email is missing", RegisterError::EmailMissing->value);

    if(strlen($email) > MAX_EMAIL_LEN) throw new RegisterException("Email is too long", RegisterError::EmailTooLong->value);
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RegisterException("Email format is incorrect", RegisterError::EmailFormatIncorrect->value);

    $existing_user = self::get_user($user["email"]);
    if($existing_user !== null) throw new RegisterException("User already exists", RegisterError::UserExists->value);

    $password = $user["password"] ?? throw new RegisterException("Password is missing", RegisterError::PasswordMissing->value);
    $password_verify = $user["password_verify"] ?? throw new RegisterException("Password verify is missing", RegisterError::PasswordVerifyMissing->value);
    if($password != $password_verify) {
      throw new RegisterException("Password and password verify don't match", RegisterError::PasswordsDontMatch->value);
    }

    $role = $user["role"] ?? throw new RegisterException("Role is missing", RegisterError::RoleMissing->value);
    if($role != 'sportsman' && $role != "trainer") throw new RegisterException("Role is incorrect", RegisterError::RoleIncorrect->value);
    
    $trainer_id = $user["trainer_id"] ?? ($user["role"] === 'sportsman' ? throw new RegisterException("Trainer id is missing", RegisterError::TrainerIdMissing->value) : null);

    $name = $user["name"] ?? throw new RegisterException("Name is missing", RegisterError::NameMissing->value);
    if(strlen($name) > 256) throw new RegisterException("Name is too long", RegisterError::NameTooLong->value);

    $sth = self::$dbh->prepare("INSERT INTO users (email, password_hash, name, role, trainer_id) VALUES (:email, :password_hash, :name, :role, :trainer_id)");
    $sth->bindParam(":email", $email, PDO::PARAM_STR);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sth->bindParam(":password_hash", $hashed_password, PDO::PARAM_STR);
    $sth->bindParam(":name", $name, PDO::PARAM_STR);
    $sth->bindParam(":role", $role, PDO::PARAM_STR);
    $sth->bindParam(":trainer_id", $trainer_id, PDO::PARAM_INT);

    return $sth->execute();
  }

  public static function get_all_trainers() {
    $result = self::$dbh->query("SELECT * FROM users u WHERE u.role = 'trainer'");
    return $result;
  }
}

Db::init();
