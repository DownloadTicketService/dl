<?php
// base functions

function returnBytes($val)
{
  if(is_int($val)) return $val;
  if(!strlen($val)) return 0;

  // parse suffix (if any)
  $val = trim($val);
  $last = strtolower($val[strlen($val)-1]);
  if($last === (string)(int)$last || $last === ".")
    return (int)$val; // no sfx

  // handle mulitiplier
  $val = (int)substr($val, 0, -1);
  switch($last)
  {
  case 'g': $val *= 1024;
  case 'm': $val *= 1024;
  case 'k': $val *= 1024;
  }
  return $val;
}
