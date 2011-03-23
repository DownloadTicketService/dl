<?php
// auxiliary functions
require_once("hooks.php");


function isTicketId($str)
{
  return (strlen($str) == 32 && preg_match("/^[a-zA-Z0-9]{32}$/", $str));
}


function isTicketExpired($DATA, $now = NULL)
{
  if(!isset($now)) $now = time();
  return ((isset($DATA["expire"]) && $DATA["expire"] < $now)
       || (isset($DATA["expire_last"]) && $DATA["expire_last"] < $now)
       || (isset($DATA["expire_dln"]) && $DATA["expire_dln"] <= $DATA["downloads"]));
}


function isGrantId($str)
{
  return isTicketId($str);
}


function isGrantExpired($DATA, $now = NULL)
{
  if(!isset($now)) $now = time();
  return (isset($$DATA["grant_expire"]) && $DATA["grant_expire"] < $now);
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
    return sprintf(T_("%s GiB"), round($size / 1073741824, 1));
  else if($size > 1048576)
    return sprintf(T_("%s MiB"), round($size / 1048576, 1));
  else if($size > 1024)
    return sprintf(T_("%s KiB"), round($size / 1024, 1));
  return sprintf(T_("%s B"), ($size? $size: 0));
}


function humanTime($seconds)
{
  if($seconds > 86400)
    return sprintf(T_("%d days"), intval($seconds / 86400));
  else if($seconds > 3600)
    return sprintf(T_("%d hours"), intval($seconds / 3600));
  else if($seconds > 60)
    return sprintf(T_("%d minutes"), intval($seconds / 60));
  return sprintf(T_("%d seconds"), ($seconds? $seconds: 0));
}


function ticketStr($DATA)
{
  return ($DATA['id'] . ' (' . $DATA['name'] . ')');
}


function ticketUrl($DATA)
{
  global $masterPath;
  return $masterPath . "?t=" . $DATA['id'];
}


function grantStr($DATA)
{
  return $DATA['id'];
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
  $addrs = explode(",", str_replace(array(";", "\n"), ",", $str));
  return join(",", array_filter(array_map('trim', $addrs)));
}


function getEMailAddrs($str)
{
  return (empty($str)? array(): explode(",", $str));
}


function includeTemplate($file, $vars = array())
{
  global $ref, $langData, $locale;
  extract($vars);
  include($file);
}


function htmlEntUTF8($string, $style = ENT_COMPAT)
{
  return htmlentities($string, $style, 'UTF-8');
}


function mailUTF8($addr, $subject, $body, $hdr)
{
  $hdr .= "\nMIME-Version: 1.0";
  $hdr .= "\nContent-Type: text/plain; charset=UTF-8";
  $hdr .= "\nContent-Transfer-Encoding: 8bit";
  $subject = mb_encode_mimeheader($subject, mb_internal_encoding(), 'Q', "\n");
  return mail($addr, $subject, $body, $hdr);
}


function errorMessage($hdr, $lines)
{
  if(!is_array($lines) || count($lines) == 1)
  {
    if(is_array($lines)) $lines = $lines[0];
    echo "<div id=\"error_message\"><label>$hdr:</label> $lines</div>";
  }
  else
  {
    echo "<div id=\"error_message\"><table><tr><td class=\"label\">"
      . "$hdr:</td><td>$lines[0]</td></tr>";
    for($i = 1; $i != count($lines); ++$i)
      echo "<tr><td></td><td>$lines[$i]</td></tr>";
    echo "</table></div>";
  }
}


function truncAtWord($str, $len, $thr = 5, $ell = "...")
{
  $min = max(0, $len - $thr);
  $max = $len - 1;
  $re = '/^(.{' . "$min,$max" . '}\S\b|.{' . $len . '}).*/u';
  return preg_replace($re, '$1...', str_replace("\n", "", $str));
}

?>
