<?php
// download ticket system
include("include/sess.php");
include("include/entry.php");

// authentication
if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
{
  $authData = array
  (
    "user" => $_SERVER['PHP_AUTH_USER'],
    "pass" => $_SERVER['PHP_AUTH_PW'],
  );
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

// handling
array_unshift($args, $msg);
include $rest[$act]['entry'];
list($error, $ret) = call_user_func_array($rest[$act]['func'], $args);
if($error !== false)
{
  call_user_func($error);
  $ret = array("error" => $ret);
}
echo json_encode($ret);

?>
