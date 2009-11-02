<?php
// initialize the session and authorization

function authenticate()
{
  global $uDb, $authRealm;

  // external authentication (built-in methods)
  foreach(Array('PHP_AUTH_USER', 'REMOTE_USER', 'REDIRECT_REMOTE_USER') as $key)
  {
    if(isset($_SERVER[$key]))
    {
      $remoteUser = $_SERVER[$key];
      break;
    }
  }

  // authentication attempt
  if(!isset($remoteUser))
  {
    if(empty($_REQUEST['u']) || !isset($_REQUEST['p']))
    {
      // simple logout
      return false;
    }

    $user = $_REQUEST['u'];
    $pass = md5($_REQUEST['p']);
  }
  else
  {
    if(isset($_REQUEST['u']) && empty($_REQUEST['u']))
    {
      // remote logout
      Header('HTTP/1.0 401 Unauthorized');
      Header('WWW-Authenticate: Basic realm="' . $authRealm . '"');
      includeTemplate('style/include/rmtlogout.php');
      exit();
    }

    $user = $remoteUser;
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
