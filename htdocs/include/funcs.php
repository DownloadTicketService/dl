<?php
// auxiliary functions

function purgeDl($key, $DATA)
{
  global $tDb, $fromAddr, $masterPath;

  if(dba_delete($key, $tDb))
  {
    unlink($DATA["path"]);

    // notify if requested
    if(!empty($DATA["email"]))
    {
      mail($DATA["email"], "[dl] $key purge notification",
	  $key . " (" . $DATA["name"] . ") was purged after " .
	  $DATA["downloads"] . " downloads from $masterPath\n",
	  "From: $fromAddr");
    }
  }
}


function humanSize($size)
{
  if($size > 1073741824)
    return intval($size / 1073741824) . " gb";
  else if($size > 1048576)
    return intval($size / 1048576) . " mb";
  else if($size > 1024)
    return intval($size / 1024) . " kb";
  return $size;
}


function humanTime($seconds)
{
  if($seconds > 86400)
    return intval($seconds / 86400) . " days";
  else if($seconds > 3600)
    return intval($seconds / 3600) . " hours";
  else if($seconds > 60)
    return intval($seconds / 60) . " minutes";
  return $seconds . " seconds";
}


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


function includeTemplate($file, $vars = array())
{
  extract($vars);
  include($file);
}
?>
