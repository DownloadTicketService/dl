<?php
// base functions

function returnBytes($val)
{
  $val = trim($val);
  $last = strtolower($val{strlen($val)-1});
  switch($last)
  {
  case 'g': $val *= 1024;
  case 'm': $val *= 1024;
  case 'k': $val *= 1024;
  }
  return $val;
}

function includeCfg()
{
  $cfgPath = "/etc/dl.php";
  foreach(explode(PATH_SEPARATOR, get_include_path()) as $path)
  {
    $tmp = "$path/config.php";
    if(is_file($tmp))
    {
      $cfgPath = $tmp;
      break;
    }
  }
  require_once($cfgPath);
}

?>
