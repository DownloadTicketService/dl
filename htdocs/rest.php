<?php
// download ticket system
include("include/init.php");
require_once("include/admfuncs.php");
require_once("include/entry.php");

// server checks
if(!isset($_SERVER["PATH_INFO"]))
{
  logError("missing PATH_INFO, cannot continue");
  httpBadRequest();
}

// ContentType is always JSON
header("Content-Type: application/json");

// authentication
$rmt = ($authRealm != false);
if(isset($_SERVER['HTTP_X_AUTHORIZATION']))
{
  $extAuth = externalAuth();
  $authData = httpBasicDecode($_SERVER['HTTP_X_AUTHORIZATION']);
  if($rmt || $extAuth !== false)
  {
    // enforce double auth/consistency when using remote authentication
    if($authData === false || $extAuth === false
    || $authData["user"] !== $extAuth["user"]
    || ($extAuth["pass"] !== false && $authData["pass"] !== $extAuth["pass"]))
    {
      logReq('inconsistent double authorization token', LOG_ERR);
      unset($authData);
    }
  }
}
if(isset($authData))
{
  if(empty($authData["user"]) || (!$rmt && empty($authData["pass"])))
  {
    logReq('missing credentials', LOG_ERR);
    httpUnauthorized();
  }
  $auth = userLogin($authData["user"], $authData["pass"], $rmt);
  unset($authData);
}
if(empty($auth))
{
  logReq('invalid credentials', LOG_ERR);
  httpUnauthorized();
}

// action
$args = explode("/", $_SERVER["PATH_INFO"]);
if($args[0] !== "" || count($args) < 2 || !isset($rest[$args[1]]))
{
  logReq('unknown request action or arguments', LOG_ERR);
  httpNotFound();
}
$act = strtolower($args[1]);
array_splice($args, 0, 2);
if($rest[$act]['admin'] && !$auth['admin'])
{
  logReq('unauthorized request', LOG_ERR);
  httpUnauthorized();
}
if($rest[$act]['method'] !== $_SERVER['REQUEST_METHOD'])
{
  logReq('bad request method', LOG_ERR);
  httpBadMethod();
}

// message
$msg = array();
if($rest[$act]['method'] == 'POST')
{
  if(empty($_POST["msg"]))
  {
    logReq('missing "msg" in POST request', LOG_ERR);
    httpBadRequest();
  }
  $msg = json_decode($_POST["msg"], true);
  if(!isset($msg))
  {
    logReq('invalid JSON in request', LOG_ERR);
    httpBadRequest();
  }
}

// expire tickets before serving any request
init();

// handling
array_unshift($args, $msg);
include $rest[$act]['entry'];
list($error, $ret) = call_user_func_array($rest[$act]['func'], $args);
if($error !== false)
{
  call_user_func($error);
  $ret = array("error" => $ret);
}
echo ($ret === false? "{}": json_encode($ret));

?>
