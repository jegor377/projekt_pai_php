<?php

function to_val($val) {
  if(isset($val) && $val !== null) {
    return 'value="' . htmlspecialchars($val, ENT_QUOTES) . '"';
  }
  return "";
}

function selected($curr, $val) {
  if(isset($curr) && $curr !== null && $curr == $val) {
    return "selected";
  }
  return "";
}