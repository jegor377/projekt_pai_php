<?php
session_start();

require_once("../lib/db.php");

header("Content-Type: application/json");
$json = file_get_contents('php://input');

switch($_SERVER['REQUEST_METHOD']) {
  case 'POST': { // register
    try {
      $user = json_decode($json, true);
      $result = Db::register_user($user);
      echo json_encode($result);
    } catch (RegisterException $e) {
      http_response_code(400);
      echo json_encode([
        'msg' => $e->getMessage(),
        'code' => $e->getCode()
      ]);
    } catch(Exception $e) {
      http_response_code(400);
      echo json_encode([
        'msg' => "Cannot register user",
        'code' => RegisterError::Other->value
      ]);
    }
  } break;
  default: {
    http_response_code(405);
  } break;
}