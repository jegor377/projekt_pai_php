<?php
session_start();
header("Content-Type: application/json");

switch($_SERVER['REQUEST_METHOD']) {
  case 'DELETE': { // logout
    session_destroy();
    echo json_encode(true);
  } break;
  default: {
    http_response_code(405);
  } break;
}