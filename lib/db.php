<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/integer.php");
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

enum ChangePasswordError: int {
  case CantFindUser = 0;
  case CurrentPasswordIncorrect = 1;
  case UnknownError = 2;
}

enum UpdateResultError: int {
  case TaskIdMissing = 0;
  case ResultIdMissing = 1;
  case ValueMissing = 2;
  case GradeMissing = 3;
  case ContestantIdMissing = 4;
  case ContestIdMissing = 5;
  case ValueIsNotInt = 6;
  case GradeIsNotInt = 7;
  case GradeIsOutOfRange = 8;
}

class RegisterException extends Exception {
  public function __construct($message = "", $code = 0) {
    parent::__construct($message, $code);
  }
}

class UpdateResultException extends Exception {
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

  public static function get_user_by_id( $id ) {
    $sth = self::$dbh->prepare("SELECT * FROM users WHERE id = :id");
    $sth->bindParam(":id", $id, PDO::PARAM_INT);
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

  public static function save_profile_picture($user_id, $url) {
    $sth = self::$dbh->prepare("UPDATE users SET avatar_url = :avatar_url WHERE id = :id");
    $sth->bindParam(":id", $user_id, PDO::PARAM_INT);
    $sth->bindParam(":avatar_url", $url, PDO::PARAM_STR);
    return $sth->execute();
  }

  public static function update_user_name($user_id, $new_name) {
    $sth = self::$dbh->prepare("UPDATE users SET name = :name WHERE id = :id");
    $sth->bindParam(":id", $user_id, PDO::PARAM_INT);
    $sth->bindParam(":name", $new_name, PDO::PARAM_STR);
    return $sth->execute();
  }

  public static function update_password($user_id, $current_password, $new_password) {
    $sth = self::$dbh->prepare("SELECT password_hash FROM users WHERE id = :id");
    $sth->bindParam(":id", $user_id, PDO::PARAM_INT);
    $sth->execute();
    $user = $sth->fetch(PDO::FETCH_ASSOC);
    if(!$user) return ChangePasswordError::CantFindUser;

    if(!password_verify($current_password, $user['password_hash'])) {
      return ChangePasswordError::CurrentPasswordIncorrect;
    }

    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $sth = self::$dbh->prepare('UPDATE users SET password_hash = :new_hash WHERE id = :id');
    $sth->bindParam(':id', $user_id, PDO::PARAM_INT);
    $sth->bindParam(':new_hash', $new_hash, PDO::PARAM_STR);
    if($sth->execute()) return null;

    return ChangePasswordError::UnknownError;
  }

  public static function get_club_by_id($club_id) {
    $sth = self::$dbh->prepare('SELECT * FROM clubs WHERE id = :id');
    $sth->bindParam(':id', $club_id, PDO::PARAM_INT);
    $sth->execute();
    $club = $sth->fetch(PDO::FETCH_ASSOC);
    return $club;
  }

  public static function get_available_clubs() {
    $sth = self::$dbh->prepare('SELECT * FROM clubs');
    $sth->execute();
    return $sth->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function get_contests_by_trainer_id($trainer_id) {
    $sth = self::$dbh->prepare('SELECT * FROM contests WHERE trainer_id = :trainer_id');
    $sth->bindParam(':trainer_id', $trainer_id, PDO::PARAM_INT);
    $sth->execute();
    $contests = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $contests;
  }

  public static function get_messages_to_user_id($user_id, $max_messages=100, $only_read_messages=false) {
    $sth = self::$dbh->prepare('SELECT * FROM (SELECT m.*, u.name AS sender_name FROM messages m LEFT JOIN users u ON (m.sender_id = u.id) WHERE receiver_id = :user_id AND (read_timestamp IS NULL OR (NOT :only_read_messages)) ORDER BY sent_timestamp DESC LIMIT :max_messages) AS sub ORDER BY sent_timestamp ASC');
    $sth->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $sth->bindParam(':max_messages', $max_messages, PDO::PARAM_INT);
    $sth->bindParam(':only_read_messages', $only_read_messages, PDO::PARAM_BOOL);
    $sth->execute();
    $messages = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $messages;
  }

  public static function read_message($message_id) {
    $sth = self::$dbh->prepare('UPDATE messages SET read_timestamp = NOW() WHERE id = :message_id');
    $sth->bindParam(':message_id', $message_id, PDO::PARAM_INT);  
    $updated = $sth->execute();
    if ($updated) {
      $sth = self::$dbh->prepare('SELECT * FROM messages WHERE id = :message_id');
      $sth->bindParam(':message_id', $message_id, PDO::PARAM_INT);
      $sth->execute();
      $message = $sth->fetch(PDO::FETCH_ASSOC);
      return $message;
    }
    return $updated;
  }

  public static function post_message($receiver_id, $sender_id, $message) {
    if($receiver_id !== 'all') {
      $sth = self::$dbh->prepare('INSERT INTO messages (receiver_id, sender_id, content) VALUES (:receiver_id, :sender_id, :content)');
      $sth->bindParam(':receiver_id', $receiver_id, PDO::PARAM_INT);
      $sth->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
      $sth->bindParam(':content', $message, PDO::PARAM_STR);
      return $sth->execute();
    } else {
      $students = self::get_trainer_students($sender_id);
      $sth = self::$dbh->prepare('INSERT INTO messages (receiver_id, sender_id, content) VALUES (:receiver_id, :sender_id, :content)');
      $student_id = null;
      $sth->bindParam(':receiver_id', $student_id, PDO::PARAM_INT);
      $sth->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
      $sth->bindParam(':content', $message, PDO::PARAM_STR);
      foreach($students as $student) {
        $student_id = $student['id'];
        if(!$sth->execute()) {
          return false;
        }
      }
      return true;
    }
  }

  public static function get_trainer_students($trainer_id) {
    $sth = self::$dbh->prepare('SELECT * FROM users WHERE trainer_id = :trainer_id');
    $sth->bindParam(':trainer_id', $trainer_id, PDO::PARAM_INT);
    $sth->execute();
    $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }

  public static function get_contest_by_id($contest_id) {
    $sth = self::$dbh->prepare('SELECT * FROM contests WHERE id = :id');
    $sth->bindParam(':id', $contest_id, PDO::PARAM_INT);
    $sth->execute();
    $rows = $sth->fetch(PDO::FETCH_ASSOC);
    return $rows;
  }

  public static function get_user_results_by_contest_id($contest_id) {
    $sth = self::$dbh->prepare('SELECT r.*, ct.name, ct.position FROM results r LEFT JOIN contest_tasks ct ON (ct.id = r.task_id) WHERE r.contest_id = :contest_id ORDER BY ct.position ASC');
    $sth->bindParam(':contest_id', $contest_id, PDO::PARAM_INT);
    $sth->execute();
    $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }

  public static function get_results_by_user_id($user_id) {
    $sth = self::$dbh->prepare('SELECT r.*, ct.name, ct.position, c.time, c.description FROM results r LEFT JOIN contest_tasks ct ON (ct.id = r.task_id) LEFT JOIN contests c ON (c.id = r.contest_id) WHERE r.contestant_id = :user_id ORDER BY ct.position ASC');
    $sth->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $sth->execute();
    $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }

  public static function get_user_results_in_contest_id($user_id, $contest_id) {
    $sth = self::$dbh->prepare('SELECT * FROM results r LEFT JOIN contest_tasks ct ON (ct.id = r.task_id) WHERE r.contestant_id = :user_id AND r.contest_id = :contest_id ORDER BY ct.position ASC');
    $sth->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $sth->bindParam(':contest_id', $contest_id, PDO::PARAM_INT);
    $sth->execute();
    $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }

  public static function get_contest_tasks_by_contest_id($contest_id) {
    $sth = self::$dbh->prepare('SELECT * FROM contest_tasks WHERE contest_id = :contest_id');
    $sth->bindParam(':contest_id', $contest_id, PDO::PARAM_INT);
    $sth->execute();
    $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $rows;
  }

  public static function get_contest_tasks_by_contest_ids($contest_ids) {
    $sth = self::$dbh->prepare('SELECT * FROM contest_tasks WHERE contest_id = :contest_id');
    $contest_id = 0;
    $result = [];
    $sth->bindParam(':contest_id', $contest_id, PDO::PARAM_INT);
    foreach($contest_ids as $id) {
      $contest_id = $id;
      $sth->execute();
      $result = array_merge($result, $sth->fetchAll(PDO::FETCH_ASSOC));
    }
    
    return $result;
  }

  public static function update_contest_primary_info($contest_id, $date, $descr, $finished) {
    $sth = self::$dbh->prepare('UPDATE contests SET time = :ctime, description = :descr, finished = :finished WHERE id = :contest_id');
    $sth->bindParam(':contest_id', $contest_id, PDO::PARAM_INT);
    $sth->bindParam(':ctime', $date, PDO::PARAM_STR);
    $sth->bindParam(':descr', $descr, PDO::PARAM_STR);
    $sth->bindParam(':finished', $finished, PDO::PARAM_BOOL);
    return $sth->execute();
  }

  public static function add_task($contest_id, $name) {
    $sth = self::$dbh->prepare('INSERT INTO contest_tasks (name, position, contest_id) VALUES (:name, COALESCE((SELECT MAX(ct2.position) FROM contest_tasks ct2 WHERE ct2.contest_id = :contest_id), 1), :contest_id)');
    $sth->bindParam(':contest_id', $contest_id, PDO::PARAM_INT);
    $sth->bindParam(':name', $name, PDO::PARAM_STR);
    return $sth->execute();
  }

  public static function delete_task($task_id) {
    $sth = self::$dbh->prepare('DELETE FROM results WHERE task_id = :task_id');
    $sth->bindParam(':task_id', $task_id, PDO::PARAM_INT);
    $sth->execute();
    $sth = self::$dbh->prepare('DELETE FROM contest_tasks WHERE id = :task_id');
    $sth->bindParam(':task_id', $task_id, PDO::PARAM_INT);
    $sth->execute();
  }

  public static function update_results($data) {
    $contest_id = $data['contest_id'] ?? throw new UpdateResultException("Nie podano id zadania", UpdateResultError::ContestIdMissing->value);
    $contestant_id = $data['contestant_id'] ?? throw new UpdateResultException("Nie podano id sportowca", UpdateResultError::ContestantIdMissing->value);
    $task_id = $data['task_id'];
    $result_id = $data['result_id'];
    $value = $data['value'];
    $grade = $data['grade'];

    $upd_sth = self::$dbh->prepare('UPDATE results SET value = :value, grade = :grade WHERE id = :result_id');

    for( $i = 0; $i < count($data['result_id']); $i++ ) {
      if(!isset($task_id[$i])) throw new UpdateResultException("Nie podano id zadania", UpdateResultError::TaskIdMissing->value);
      if(!isset($value[$i])) throw new UpdateResultException("Nie podano wyniku", UpdateResultError::ValueMissing->value);
      if(!isset($grade[$i])) throw new UpdateResultException("Nie podano oceny", UpdateResultError::GradeMissing->value);

      if($value[$i] === '' && $grade[$i] === '') continue;

      if(!is_str_int($value[$i])) throw new UpdateResultException("Wynik nie jest liczbą", UpdateResultError::ValueIsNotInt->value);
      if(!is_str_int($grade[$i])) throw new UpdateResultException("Ocena nie jest liczbą", UpdateResultError::GradeIsNotInt->value);
      if($grade[$i] < 1 || $grade[$i] > 10) throw new UpdateResultException("Ocena jest po za zakresem (1 - 10)", UpdateResultError::GradeIsOutOfRange->value);

      if($result_id[$i] === 'new') {
        $sth = self::$dbh->prepare('INSERT INTO results (task_id, contest_id, contestant_id, value, grade) VALUES (:task_id, :contest_id, :contestant_id, :value, :grade)');
        $sth->bindParam(':task_id', $task_id[$i], PDO::PARAM_INT);
        $sth->bindParam(':contest_id', $contest_id, PDO::PARAM_INT);
        $sth->bindParam(':contestant_id', $contestant_id, PDO::PARAM_INT);
        $sth->bindParam(':value', $value[$i], PDO::PARAM_INT);
        $sth->bindParam(':grade', $grade[$i], PDO::PARAM_INT);
        $sth->execute();
      } else {
        $upd_sth->bindParam(':value', $value[$i], PDO::PARAM_INT);
        $upd_sth->bindParam(':grade', $grade[$i], PDO::PARAM_INT);
        $upd_sth->bindParam(':result_id', $result_id[$i], PDO::PARAM_INT);
        $upd_sth->execute();
      }
    }
  }

  public static function add_contest($trainer_id) {
    $sth = self::$dbh->prepare('INSERT INTO contests (trainer_id) VALUES (:trainer_id)');
    $sth->bindParam(':trainer_id', $trainer_id, PDO::PARAM_INT);
    if($sth->execute()) return self::$dbh->lastInsertId();
    return null;
  }

  public static function change_club($user_id, $club_id) {
    $sth = self::$dbh->prepare('SELECT * FROM clubs WHERE id = :club_id');
    $sth->bindParam(':club_id', $club_id, PDO::PARAM_INT);
    $sth->execute();
    if($sth->fetch(PDO::FETCH_ASSOC)) {
      $sth = self::$dbh->prepare('UPDATE users SET club_id = :club_id WHERE id = :user_id');
      $sth->bindParam(':user_id', $user_id, PDO::PARAM_INT);
      $sth->bindParam(':club_id', $club_id, PDO::PARAM_INT);
      return $sth->execute();
    }
    return false;
  }
}

Db::init();
