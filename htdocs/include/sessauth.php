<?php
// initialize session _and_ authorization
require_once("sess.php");

function authenticate()
{
  global $db, $authRealm, $style;

  $rmt = ($authRealm != false);
  if(!$rmt)
  {
    if(empty($_REQUEST['u']) || !isset($_POST['p']))
    {
      // simple logout
      return false;
    }

    // built-in authentication attempt
    $authData = Array
    (
      "user" => $_REQUEST['u'],
      "pass" => $_POST['p'],
      "email" => false
    );
  }
  else
  {
    if(isset($_REQUEST['u']) && empty($_REQUEST['u']))
    {
      // remote logout
      header('HTTP/1.0 401 Unauthorized');
      header('WWW-Authenticate: Basic realm="' . $authRealm . '"');
      includeTemplate("$style/include/rmtlogout.php");
      return null;
    }

    // external authentication
    $authData = externalAuth();
    if($authData === false)
    {
      // missing remote authentication data
      logError('missing remote authentication data');
      httpInternalError();
    }
  }

  // verify if we have administration rights
  $DATA = userLogin($authData["user"], $authData["pass"], $rmt, $authData["email"]);

  // check if the external authenticator provides an email address
  if($DATA !== false && empty($DATA["email"]))
    $DATA['email'] = $authData["email"];

  return $DATA;
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
  {
    restart_session();
    $_REQUEST['token'] = $token;
  }
  elseif(session_id())
  {
    logout();
    exit();
  }
}
