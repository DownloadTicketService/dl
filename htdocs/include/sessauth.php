<?php
// initialize session _and_ authorization
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
    $pass = false;
  }

  // verify if we have administration rights
  return userLogin($user, $pass, isset($remoteUser));
}


function logout()
{
  $name = session_name();
  $params = session_get_cookie_params();
  session_destroy();
  setcookie($name, '', 1,
      $params["path"], $params["domain"],
      $params["secure"], $params["httponly"]);
}


if(!isset($auth) || isset($_REQUEST['u']))
{
  $auth = authenticate();
  if(isset($auth))
    session_regenerate_id();
  elseif(session_id())
  {
    logout();
    exit();
  }
}
?>
