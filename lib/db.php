<?php
require_once("config.php");

class Db {
  private $dbh = null;
  
  public function init() {
    if ($this->dbh == null) {
      $this->dbh = new PDO("mysql:host=localhost;dbname=" . Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
    }
  }

  public function get_user($email) {
    $sth = $this->dbh->prepare("SELECT * FROM users WHERE email = :email");
    $sth->bindParam(":email", $email, PDO::PARAM_STR);
    $sth->execute();
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    if($row) {
      return $row;
    }
    return null;
  }
}

