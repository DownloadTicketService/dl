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

  if(isset($auth['name']))
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
    return sprintf(T_("%s gb"), round($size / 1073741824, 3));
  else if($size > 1048576)
    return sprintf(T_("%s mb"), round($size / 1048576, 3));
  else if($size > 1024)
    return sprintf(T_("%s kb"), round($size / 1024, 3));
  return sprintf(T_("%s bytes"), $size);
}


function humanTime($seconds)
{
  if($seconds > 86400)
    return sprintf(T_("%d days"), intval($seconds / 86400));
  else if($seconds > 3600)
    return sprintf(T_("%d hours"), intval($seconds / 3600));
  else if($seconds > 60)
    return sprintf(T_("%d minutes"), intval($seconds / 60));
  return sprintf(T_("%d seconds"), $seconds);
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
  return join(",", array_filter(array_map('trim', $addrs)));
}


function getEMailAddrs($str)
{
  return (empty($str)? array(): split(",", $str));
}


function includeTemplate($file, $vars = array())
{
  global $ref, $langData, $locale;
  extract($vars);
  include($file);
}


function genTicketId($seed)
{
  global $dataDir;

  // generate new unique id/file name
  if(!file_exists($dataDir)) mkdir($dataDir);
  do
  {
    list($usec, $sec) = microtime();
    $id = md5(rand() . "/$usec/$sec/" . $seed);
    $tmpFile = "$dataDir/$id";
  }
  while(fopen($tmpFile, "x") === FALSE);

  return array($id, $tmpFile);
}


function htmlEntUTF8($string, $style = ENT_COMPAT)
{
  return htmlentities($string, $style, 'UTF-8');
}


function mailUTF8($addr, $subject, $body, $hdr)
{
  $hdr .= "\nContent-type: text/plain; charset=UTF-8";
  $hdr .= "\nContent-Transfer-Encoding: 8bit";
  $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
  return mail($addr, $subject, $body, $hdr);
}

?>
