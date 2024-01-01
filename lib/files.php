<?php

function was_uploaded($name) {
  return file_exists($_FILES[$name]['tmp_name']) && is_uploaded_file($_FILES[$name]['tmp_name']);
}