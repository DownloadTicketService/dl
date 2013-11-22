<?php
// download ticket system
include("include/init.php");
require_once("include/admfuncs.php");
require_once("include/entry.php");

// ContentType is always JSON
header("Content-Type: application/json");

// authentication
if(isset($_SERVER['HTTP_X_AUTHORIZATION']))
{
  $extAuth = externalAuth();
  $authData = httpBasicDecode($_SERVER['HTTP_X_AUTHORIZATION']);
  if($authData === false || $extAuth === false
  || $authData["user"] !== $extAuth["user"]
  || ($extAuth["pass"] !== false && $authData["pass"] !== $extAuth["pass"]))
    unset($authData);
}
if(isset($authData))
{
  $rmt = ($authRealm != false);
  if(empty($authData["user"]) || (!$rmt && empty($authData["pass"])))
    httpUnauthorized();
  $auth = userLogin($authData["user"], $authData["pass"], $rmt);
  unset($authData);
}
if(empty($auth))
  httpUnauthorized();

// action
if(!isset($_SERVER["PATH_INFO"]))
  httpBadRequest();
$args = explode("/", $_SERVER["PATH_INFO"]);
if($args[0] !== "" || count($args) < 2 || !isset($rest[$args[1]]))
  httpNotFound();
$act = strtolower($args[1]);
array_splice($args, 0, 2);
if($rest[$act]['admin'] && !$auth['admin'])
  httpUnauthorized();
if($rest[$act]['method'] !== $_SERVER['REQUEST_METHOD'])
  httpBadMethod();

// message
$msg = array();
if($rest[$act]['method'] == 'POST')
{
  if(empty($_POST["msg"]))
    httpBadRequest();
  $msg = json_decode($_POST["msg"], true);
  if(!isset($msg))
    httpBadRequest();
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
