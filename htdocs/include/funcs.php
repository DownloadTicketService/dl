<?php
// auxiliary functions
require_once("hooks.php");


function ticketPurge($DATA, $auto = true)
{
  global $db;

  if($db->exec("DELETE FROM ticket WHERE id = ". $db->quote($DATA["id"])) == 1)
  {
    unlink($DATA["path"]);
    onTicketPurge($DATA, $auto);
  }
}


function grantPurge($DATA, $auto = true)
{
  global $db;

  if($db->exec("DELETE FROM grant WHERE id = ". $db->quote($DATA["id"])) == 1)
    onGrantPurge($DATA, $auto);
}


function logEvent($logLine)
{
  global $logFile, $useSysLog, $logFd, $auth;
  if(empty($logFile)) return;

  if(isset($auth))
    $logLine = $auth['name'] . ': ' . $logLine;

  if($useSysLog)
    syslog(LOG_INFO, $logLine);
  elseif(isset($logFd))
  {
    $logLine = "[" . date(DATE_RSS) . "] $logLine\n";
    flock($logFd, LOCK_EX);
    fseek($logFd, 0, SEEK_END);
    fwrite($logFd, $logLine);
    fflush($logFd);
    flock($logFd, LOCK_UN);
  }
}


function logTicketEvent($DATA, $logLine)
{
  logEvent('t/' . ticketStr($DATA) . ": $logLine");
}


function logGrantEvent($DATA, $logLine)
{
  logEvent('g/' . grantStr($DATA) . ": $logLine");
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


function humanTicketStr($DATA)
{
  $str = '"' . $DATA['name'] . '"';
  if(!empty($DATA['cmt'])) $str .= ' (' . $DATA['cmt'] . ')';
  return $str;
}


function ticketStr($DATA)
{
  $str = $DATA['id'] . ' (' . $DATA['name'];
  if(!empty($DATA['cmt'])) $str .= ': ' . $DATA['cmt'];
  $str .= ')';
  return $str;
}


function ticketUrl($DATA)
{
  global $masterPath;
  return $masterPath . "?t=" . $DATA['id'];
}


function grantStr($DATA)
{
  $str = $DATA['id'];
  if(!empty($DATA['cmt'])) $str .= ' (' . $DATA['cmt'] . ')';
  return $str;
}


function grantUrl($DATA)
{
  global $masterPath;
  return $masterPath . "?g=" . $DATA['id'];
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


function fixEMailAddrs($str)
{
  $addrs = split(",", str_replace(array(";", "\n"), ",", $str));
  return join(",", array_filter(array_map(trim, $addrs)));
}


function getEMailAddrs($str)
{
  return (empty($str)? array(): split(",", $str));
}


function includeTemplate($file, $vars = array())
{
  extract($vars);
  include($file);
}
?>
