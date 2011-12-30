<?php
// download ticket system
include("include/sess.php");
include("include/entry.php");

// in the REST interface, everything is expected to be contained in a "msg"
// parameter, encoded in JSON. Every request requires authentication in an
// optional "auth" parameter (again in JSON) and everything is stateless.
// Authentication and validation are performed here, before passing the control
// on to the real handler. I/O in itself is freeform depending on the request.
if(!isset($_SERVER["PATH_INFO"])
|| !isset($_POST["msg"]))
  httpBadRequest();

// action
$args = explode("/", $_SERVER["PATH_INFO"]);
if($args[0] !== "" || count($args) < 2 || !isset($rest[$args[1]]))
  httpBadRequest();
$act = strtolower($args[1]);
array_splice($args, 0, 2);

// authentication
if(!isset($auth) || isset($_POST["auth"]))
{
  if(isset($_POST["auth"]))
  {
    $remoteUser = false;
    $authData = json_decode($_POST["auth"], true, 2);
  }
  else
  {
    // external authentication
    foreach(Array('PHP_AUTH_USER', 'REMOTE_USER', 'REDIRECT_REMOTE_USER') as $key)
    {
      if(isset($_SERVER[$key]))
      {
	$remoteUser = true;
	$authData = array("user" => $_SERVER[$key], "pass" => false);
	break;
      }
    }
  }
}
if(isset($authData))
{
  if(empty($authData["user"]) || (!$remoteUser && empty($authData["pass"])))
    httpBadRequest();
  $auth = userLogin($authData["user"], $authData["pass"], $remoteUser);
  unset($authData);
}
if(empty($auth) || ($rest[$act]['admin'] && !$auth['admin']))
{
  header('HTTP/1.0 401 Unauthorized');
  header('WWW-Authenticate: Basic realm="' . $authRealm . '"');
  exit();
}

// message
$msg = json_decode($_POST["msg"], true);
if(!isset($msg))
  httpBadRequest();

// handling
array_unshift($args, $msg);
include $rest[$act]['entry'];
$ret = call_user_func_array($rest[$act]['func'], $args);
echo json_encode($ret);

?>
