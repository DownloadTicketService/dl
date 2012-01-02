<?php
// initialize the session and authorization
require_once("sess.php");

function authenticate()
{
  global $db, $authRealm;

  // external authentication (built-in methods)
  if($authRealm)
  {
    foreach(Array('PHP_AUTH_USER', 'REMOTE_USER', 'REDIRECT_REMOTE_USER') as $key)
    {
      if(isset($_SERVER[$key]))
      {
	$remoteUser = $_SERVER[$key];
	break;
      }
    }
  }

  // authentication attempt
  if(!isset($remoteUser))
  {
    if(empty($_REQUEST['u']) || !isset($_POST['p']))
    {
      // simple logout
      return false;
    }

    $user = $_REQUEST['u'];
    $pass = $_POST['p'];
  }
  else
  {
    if(isset($_REQUEST['u']) && empty($_REQUEST['u']))
    {
      // remote logout
      header('HTTP/1.0 401 Unauthorized');
      header('WWW-Authenticate: Basic realm="' . $authRealm . '"');
      includeTemplate('style/include/rmtlogout.php');
      return null;
    }

    $user = $remoteUser;
  }

  // verify if we have administration rights
  return userLogin($user, $pass, isset($remoteUser));
}

if(!isset($auth) || isset($_REQUEST['u']))
{
  $auth = authenticate();
  if(!isset($auth))
  {
    session_destroy();
    exit();
  }
}
?>
