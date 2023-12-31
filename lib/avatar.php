<?php

function get_avatar_url($user) {
  $avatar_url = "/images/avatar.webp";
  if(isset($user['avatar_url'])) {
    $avatar_url = $user['avatar_url'];
  }
  return $avatar_url;
}