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


function ticketExpiry($DATA)
{
  if($DATA["expire_dln"] || $DATA["last_time"])
  {
    if($DATA["expire_last"])
      return sprintf(T_("About %s"), humanTime($DATA["expire_last"] - time()));
    elseif($DATA["expire_dln"] && $DATA["downloads"])
      return sprintf(T_("About %d downloads"), ($DATA["expire_dln"] - $DATA["downloads"]));
    elseif($DATA["expire"])
      return sprintf(T_("About %s"), humanTime($DATA["expire"] - time()));
    elseif($DATA["expire_dln"])
      return sprintf(T_("After %d downloads"), $DATA["expire_dln"]);
    else
      return sprintf(T_("%s after next download"), humanTime($DATA["last_time"]));
  }
  elseif($DATA["expire"])
    return sprintf(T_("In %s"), humanTime($DATA["expire"] - time()));

  return ("<strong>" . T_("Never") . "</strong>");
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


function grantExpiry($DATA)
{
  if($DATA["grant_expire"])
    return sprintf(T_("In %s"), humanTime($DATA["grant_expire"] - time()));

  return ("<strong>" . T_("Never") . "</strong>");
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


function genericMessage($class, $hdr, $lines)
{
  if(!is_array($lines) || count($lines) == 1)
  {
    if(is_array($lines)) $lines = $lines[0];
    echo "<div class=\"$class\"><label>$hdr:</label> $lines</div>";
  }
  else
  {
    echo "<div class=\"$class\"><table><tr><td class=\"label\">"
      . "$hdr:</td><td>$lines[0]</td></tr>";
    for($i = 1; $i != count($lines); ++$i)
      echo "<tr><td></td><td>$lines[$i]</td></tr>";
    echo "</table></div>";
  }
}


function errorMessage($hdr, $lines)
{
  genericMessage('error_message', $hdr, $lines);
}


function infoMessage($hdr, $lines)
{
  genericMessage('info_message', $hdr, $lines);
}


function truncAtWord($str, $len, $thr = 5, $ell = "\xE2\x80\xA6")
{
  $min = max(0, $len - $thr);
  $max = $len - 1;
  $re = '/^(.{' . "$min,$max" . '}\S\b|.{' . $len . '}).*/u';
  $rp = '$1' . $ell;
  return preg_replace($re, $rp, str_replace("\n", "", $str));
}


function sliceWords($str, $len)
{
  return preg_replace('/(\S{' . $len . '})/u', "$1\xE2\x80\x8B", $str);
}


function is_numeric_int($str)
{
  return (is_int($str) || (int)$str == $str);
}


function anyOf()
{
  foreach(func_get_args() as $arg)
    if(isset($arg)) return $arg;
  return NULL;
}


function not_empty(&$v)
{
  return !empty($v);
}


function validateParams(&$params, &$array)
{
  $found = false;
  $error = false;

  foreach($params as $k => $v)
  {
    $p = &$array[$k];
    if(isset($p))
    {
      if(!is_array($v))
	$v = array($v);

      foreach($v as $i)
      {
	if(call_user_func($i, $p))
	  $found = true;
	else
	{
	  $error = true;
	  unset($array[$k]);
	  break;
	}
      }
    }
  }

  return ($found && !$error);
}

?>
