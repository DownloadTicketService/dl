<?php
// base functions

function returnBytes($val)
{
  if(!strlen($val)) return 0;
  if(is_int($val)) return $val;

  // parse suffix (if any)
  $val = trim($val);
  $last = strtolower($val{strlen($val)-1});
  if($last === (string)(int)$last || $last === ".")
    return $val; // no sfx

  // handle mulitiplier
  $val = substr($val, 0, -1);
  switch($last)
  {
  case 'g': $val *= 1024;
  case 'm': $val *= 1024;
  case 'k': $val *= 1024;
  }
  return $val;
}
