<?php

function group_by_name($array, $name) {
  $res = [];

  foreach($array as $element) {
    $res[$element[$name]] []= $element;
  }

  return $res;
}

function average_obj_array($array, $name) {
  $size = count($array);
  if($size == 0) return null;

  $values = array_column($array, $name);

  return array_sum($values) / $size;
}