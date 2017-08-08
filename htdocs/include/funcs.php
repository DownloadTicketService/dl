<?php
// auxiliary functions
require_once("hooks.php");


function isTicketId($str)
{
  global $tokenLenght;
  return (strlen($str) == $tokenLenght && preg_match("/^[a-zA-Z0-9]*$/", $str));
}


function isGrantId($str)
{
  return isTicketId($str);
}


function logEvent($logLine, $logType = LOG_INFO)
{
  global $logFile, $useSysLog, $logFd, $auth;

  $attr = array();
  if(isset($auth['name']))
    $attr[] = $auth['name'];
  if(isset($_SERVER['REMOTE_ADDR']))
    $attr[] = $_SERVER['REMOTE_ADDR'];
  if(count($attr))
    $logLine = '[' . implode(", ", $attr) . '] ' . $logLine;
  if($logType == LOG_ERR)
    $logLine = 'error: ' . $logLine;

  if($useSysLog)
    syslog($logType, $logLine);
  elseif(!isset($logFd))
  {
    if($logType == LOG_ERR)
      error_log('DL: ' . $logLine);
  }
  else
  {
    $logLine = "[" . date(DATE_W3C) . "] $logLine\n";
    flock($logFd, LOCK_EX);
    fseek($logFd, 0, SEEK_END);
    fwrite($logFd, $logLine);
    fflush($logFd);
    flock($logFd, LOCK_UN);
  }
}


function logError($logLine)
{
  logEvent($logLine, LOG_ERR);
}


function logDBError($obj, $logLine)
{
  $err = $obj->errorInfo();
  logError($logLine . ': ' . $err[2]);
}


function logTicketEvent($DATA, $logLine, $logType = LOG_INFO)
{
  logEvent('t/' . ticketStr($DATA) . ": $logLine", $logType);
}


function logGrantEvent($DATA, $logLine, $logType = LOG_INFO)
{
  logEvent('g/' . grantStr($DATA) . ": $logLine", $logType);
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
  if($seconds > 31536000)
    return sprintf(T_("%d years"), intval($seconds / 31536000));
  else if($seconds > 2592000)
    return sprintf(T_("%d months"), intval($seconds / 2592000));
  else if($seconds > 86400)
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


function fixEMailAddrs($str)
{
  $addrs = explode(",", str_replace(array(";", "\n"), ",", $str));
  return join(",", array_filter(array_map('trim', $addrs)));
}


function getEMailAddrs($str)
{
  return (empty($str)? array(): explode(",", $str));
}


function is_email($str)
{
  return is_string($str) && (empty($str) || filter_var($str, FILTER_VALIDATE_EMAIL));
}


function is_email_list($str)
{
  if(!is_string($str)) return false;
  foreach(getEmailAddrs(fixEMailAddrs($str)) as $addr)
  {
    if(!filter_var($addr, FILTER_VALIDATE_EMAIL))
      return false;
  }
  return true;
}


function is_email_list1($str)
{
  if(!is_string($str)) return false;
  $addrs = getEmailAddrs(fixEMailAddrs($str));
  return count($addrs) && filter_var($addrs[0], FILTER_VALIDATE_EMAIL);
}


function includeTemplate($file, $vars = array())
{
  global $ref, $langData, $locale, $style, $banner;
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


function infoTable($lines)
{
  echo "<div class=\"info_message\"><table>";
  foreach($lines as $hdr => $line)
    echo "<tr><td class=\"label\">$hdr:</td><td>$line</td></tr>";
  echo "</table></div>";
}


function uploadErrorStr()
{
  global $UPLOAD_ERRNO;
  switch($UPLOAD_ERRNO)
  {
  case UPLOAD_ERR_INI_SIZE:
  case UPLOAD_ERR_FORM_SIZE:
    $msg = T_("file too big");
    break;

  case UPLOAD_ERR_PARTIAL:
  case UPLOAD_ERR_NO_FILE:
    $msg = T_("upload interrupted");
    break;

  default:
    $msg = T_("internal error");
  }

  return $msg;
}


function is_numeric_int($str)
{
  return (is_int($str) || (int)$str == $str);
}


function to_boolean($v)
{
  if(is_bool($v)) return $v;
  elseif($v === 1 || $v === "1" || $v === "true") return true;
  elseif($v === 0 || $v === "0" || $v === "false") return false;
  return null;
}


function is_boolean($str)
{
  return !is_null(to_boolean($str));
}


function is_expiry_choice($v)
{
  return is_string($v) && in_array($v, array("auto", "once", "never", "custom"));
}


function anyOf()
{
  foreach(func_get_args() as $arg)
    if(isset($arg)) return $arg;
  return NULL;
}


function not_empty(&$v)
{
  return isset($v) && !empty(trim($v));
}


function is_token($v)
{
  return (is_string($v) && isset($_SESSION['token']) && $v === $_SESSION['token']);
}


function check_token()
{
  return (isset($_REQUEST['token']) && is_token($_REQUEST['token']));
}


function tokenUrl($url, $params = array())
{
  $url .= '?token=' . urlencode($_SESSION['token']);
  foreach($params as $k => $v)
  {
    $url .= '&' . urlencode($k);
    if(!is_null($v))
      $url .= '=' . urlencode($v);
  }
  return $url;
}


function check_referer()
{
  global $masterPath;

  if(empty($_SERVER['HTTP_REFERER']))
    return false;

  $refPath = substr($_SERVER['HTTP_REFERER'], 0, strlen($masterPath));
  return ($refPath == $masterPath);
}


function validateParams(&$params, &$array)
{
  // check required parameters first
  foreach($params as $k => $v)
  {
    if(!is_array($v) || !@$v['required'])
      continue;

    if(!isset($array[$k]))
      return false;
  }

  // validation functions
  $error = false;

  foreach($params as $k => $v)
  {
    $p = &$array[$k];
    if(isset($p))
    {
      if($v === false)
	unset($array[$k]);

      if(!is_array($v))
	$v = array('funcs' => array($v));

      foreach($v['funcs'] as $i)
      {
	if(!call_user_func($i, $p))
	{
	  $error = true;
	  unset($array[$k]);
	  break;
	}
      }
    }
  }

  return !$error;
}


function randomToken()
{
  global $tokenLenght;
  return bin2hex(openssl_random_pseudo_bytes($tokenLenght / 2));
}


function httpBasicDecode($hdr)
{
  // check minimal lenght and header
  if(strlen($hdr) < 14 || substr($hdr, 0, 6) !== "Basic ")
    return false;

  // decode the data
  $data = base64_decode(substr($hdr, 6));
  if($data === false)
    return false;

  $colon = strpos($data, ':');
  if($colon === false)
    return false;

  $user = substr($data, 0, $colon);
  $pass = substr($data, $colon + 1);
  return array("user" => $user, "pass" => $pass);
}


function externalAuth()
{
  $user = false;
  foreach(Array('PHP_AUTH_USER', 'REMOTE_USER', 'REDIRECT_REMOTE_USER') as $key)
  {
    if(isset($_SERVER[$key]))
    {
      $user = $_SERVER[$key];
      break;
    }
  }
  if($user === false)
    return false;

  $pass = (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])?
      $_SERVER['PHP_AUTH_PW']: false);

  $email = (isset($_SERVER['HTTP_USER_EMAIL'])? $_SERVER['HTTP_USER_EMAIL']: false);

  return array("user" => $user, "pass" => $pass, "email" => $email);
}


function mb_basename($path)
{
  $path = preg_replace('/[\/\\\:]+$/u', '', $path);
  return preg_replace('/.*[\/\\\:]/u', '', $path);
}

function mb_sanitize($path)
{
  return preg_replace('/([\/\\\:?%*|"<>[:cntrl:]])+/u', '_', trim($path));
}

function mb_sane_base($path)
{
  $base = mb_basename($path);
  return mb_sanitize(mb_strlen($base)? $base: $path);
}


function uniqueFileName($name, &$usedNames)
{
  if(!isset($usedNames[$name]))
  {
    $usedNames[$name] = true;
    return $name;
  }

  for($i = 2;; ++$i)
  {
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    if(!strlen($ext))
      $tmp = sprintf("%s_%d", $name, $i);
    else
    {
      $base = substr($name, 0, -strlen($ext) - 1);
      $tmp = sprintf("%s_%d.%s", $base, $i, $ext);
    }
    if(!isset($usedNames[$tmp]))
    {
      $usedNames[$tmp] = true;
      return $tmp;
    }
  }
}


function uploadedFiles(&$FILES)
{
  global $UPLOAD_ERRNO;
  if(empty($FILES))
    return false;

  // uniform single files to array
  if(!is_array($FILES["tmp_name"]))
  {
    foreach(array_keys($FILES) as $k)
      $FILES[$k] = array($FILES[$k]);
  }

  // check individual files
  $ret = array();
  for($i = 0; $i != count($FILES["tmp_name"]); ++$i)
  {
    if(is_uploaded_file($FILES["tmp_name"][$i]) && $FILES["error"][$i] == UPLOAD_ERR_OK)
    {
      $file = array();
      foreach(array_keys($FILES) as $k)
	$file[$k] = $FILES[$k][$i];
      $ret[] = $file;
    }
    elseif($FILES["error"][$i] != UPLOAD_ERR_NO_FILE)
    {
      $UPLOAD_ERRNO = $FILES["error"][$i];
      return false;
    }
  }
  if(!count($ret))
  {
    $UPLOAD_ERRNO = UPLOAD_ERR_NO_FILE;
    return false;
  }

  // fix file size overflow (when possible) in php 5.4-5.5
  foreach($ret as &$FILE)
  {
    if($FILES['size'] < 0)
    {
      $FILE['size'] = filesize($FILE["tmp_name"]);
      if($FILE['size'] < 0)
      {
	logError($FILE["tmp_name"] . ": uncorrectable PHP file size overflow");
	$UPLOAD_ERRNO = UPLOAD_ERR_EXTENSION;
	return false;
      }
    }
  }

  return $ret;
}


function handleUpload($FILES)
{
  global $UPLOAD_ERRNO;

  // generate new unique id/file name
  list($id, $tmpFile) = genTicketId();
  $files = array();
  if(count($FILES) == 1)
  {
    if(!move_uploaded_file($FILES[0]["tmp_name"], $tmpFile))
    {
      logError("cannot move file " . $FILES[0]["tmp_name"] . " into $tmpFile");
      goto error;
    }
    $name = mb_sane_base($FILES[0]["name"]);
    $files[$name] = true;
    $size = $FILES[0]["size"];
  }
  else
  {
    $zip = new ZipArchive();
    if($zip->open($tmpFile, ZipArchive::CREATE) !== true)
      goto error;
    foreach($FILES as $FILE)
    {
      $name = uniqueFileName(mb_sane_base($FILE["name"]), $files);
      if(!$zip->addFile($FILE["tmp_name"], $name))
	goto error;
    }
    if(!$zip->close())
      goto error;
    $name = "Archive-" . date("Y-m-d") . ".zip";
    $size = filesize($tmpFile);
  }

  return array('id'=>$id, 'path'=>$tmpFile, 'files'=>array_keys($files),
	       'name'=>$name, 'size'=>$size);

 error:
  $UPLOAD_ERRNO = UPLOAD_ERR_EXTENSION;
  unlink($tmpFile);
  return false;
}


function withUpload($FILES, $func, $params)
{
  $ret = false;
  $upload = handleUpload($FILES);
  reconnectDB();
  if($upload !== false)
  {
    $ret = call_user_func_array($func, array_merge(array($upload), $params));
    if($ret === false)
      unlink($upload['path']);
  }
  return $ret;
}


function hashPassword($pass)
{
  // for compatibility with pass_ph (PasswordHash), we enforce PASSWORD_BCRYPT
  $ret = password_hash($pass, PASSWORD_BCRYPT);
  if($ret === false)
    throw new Exception("password_hash failure");
  return $ret;
}
