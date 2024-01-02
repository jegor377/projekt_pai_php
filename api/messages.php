<?php

session_start();

require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/db.php");
require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/auth.php");

if(isset($_SESSION["user_id"])) {
  $user = Db::get_user_by_id($_SESSION['user_id']);
} else {
  header("Location: /login.php");
  exit();
}

enum MessageErrors: int {
  case ReceiverMissing = 1;
  case MessageMissing = 2;
}

class MessageException extends Exception {
  public function __construct(string $message, int $code) {
    parent::__construct($message, $code);
  }
}

$data = json_decode(file_get_contents('php://input'), true);

header("Content-Type: application/json");

try {
  switch($_SERVER['REQUEST_METHOD']) {
    case 'GET': { // read messages
      $user_id = $user['id'];
      $start_time = $_GET['start_time'] ?? null;
      $end_time = $_GET['end_time'] ?? null;
      $messages = Db::get_messages_to_user_id($user_id, $start_time, $end_time);

      echo json_encode($messages);
    } break;
    case 'POST': { // post message
      $sender_id = $user['id'];
      $receiver_id = $data["receiver_id"] ?? throw new MessageException("Receiver id missing", MessageErrors::ReceiverMissing->value);
      $message = $data['message'] ?? throw new MessageException('Message missing', MessageErrors::MessageMissing->value);

      $result = Db::post_message($receiver_id, $sender_id, $message);

      echo json_encode($result);
    } break;
    default: {
      http_response_code(405);
    } break;
  }
} catch(MessageException $e) {
  http_response_code(400);

  echo json_encode([
    'error'=> $e->getMessage(),
    'code'=> $e->getCode()
  ]);
}