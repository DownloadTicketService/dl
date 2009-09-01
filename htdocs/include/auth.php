<?php
// initialize the session and authorization

function authenticate()
{
  global $uDb;

  // external authentication (built-in methods)
  foreach(Array('PHP_AUTH_USER', 'REMOTE_USER', 'REDIRECT_REMOTE_USER') as $key)
  {
    if(isset($_SERVER[$key]))
    {
      $remoteUser = $_SERVER[$key];
      break;
    }
  }

  // external authentication (external methods)
  if(!isset($remoteUser))
  {
    foreach(Array('REMOTE_AUTHORIZATION', 'REDIRECT_REMOTE_AUTHORIZATION') as $key)
    {
      if(isset($_SERVER[$key]))
      {
	list($remoteUser) = explode(':', base64_decode(substr($_SERVER[$key], 6)));
	break;
      }
    }
  }

  // authentication attempt
  if(isset($remoteUser))
    $user = $remoteUser;
  else
  {
    if(empty($_REQUEST['u']) || !isset($_REQUEST['p']))
      return false;

    $user = $_REQUEST['u'];
    $pass = md5($_REQUEST['p']);
  }

  // verify if we have administration rights
  $DATA = dba_fetch($user, $uDb);
  if($DATA === false)
  {
    $okpass = isset($remoteUser);
    $admin = false;
  }
  else
  {
    $DATA = unserialize($DATA);
    $okpass = (isset($remoteUser) || ($pass === $DATA['pass']));
    $admin = $DATA['admin'];
  }

  if(!$okpass) return false;
  return array('user' => $user, 'admin' => $admin);
}

session_name($sessionName);
session_start();
if(!isset($_SESSION["auth"]) || isset($_REQUEST['u']))
  $_SESSION["auth"] = authenticate();
$auth = &$_SESSION["auth"];

?>
