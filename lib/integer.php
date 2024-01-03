<?php

function is_str_int($value) {
  return is_numeric($value) ? intval($value) == $value : false;
}