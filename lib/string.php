<?php

function shortcut($str, $len) {
  if(strlen($str) > $len) {
    return substr($str,0, $len) . '...';
  }
  return $str;
}