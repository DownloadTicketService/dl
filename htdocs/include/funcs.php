<?php
// auxiliary functions
require_once("hooks.php");


function purgeDl($DATA, $auto = true)
{
  global $tDb;

  if(dba_delete($DATA["id"], $tDb))
  {
    unlink($DATA["path"]);
    onPurge($DATA, $auto);
  }
}


function logEvent($logLine)
{
  global $logFile, $useSysLog, $logFd;
  if(empty($logFile)) return;

  if($useSysLog)
    syslog(LOG_INFO, $logLine);
  elseif(isset($logFd))
  {
    $logLine = "[" . date(DATE_RSS) . "] " . $logLine . "\n";
    flock($logFd, LOCK_EX);
    fseek($logFd, 0, SEEK_END);
    fwrite($logFd, $logLine);
    fflush($logFd);
    flock($logFd, LOCK_UN);
  }
}


function humanSize($size)
{
  if($size > 1073741824)
    return round($size / 1073741824, 3) . " gb";
  else if($size > 1048576)
    return round($size / 1048576, 3) . " mb";
  else if($size > 1024)
    return round($size / 1024, 3) . " kb";
  return $size . " bytes";
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
