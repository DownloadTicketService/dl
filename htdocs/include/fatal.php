<?php
// some helpers for fatal errors
function httpBadRequest()
{
  header("HTTP/1.0 400 Bad Request");
  exit();
}

function httpUnauthorized()
{
  global $authRealm;
  header('HTTP/1.0 401 Unauthorized');
  if($authRealm) header('WWW-Authenticate: Basic realm="' . $authRealm . '"');
  exit();
}

function httpNotFound()
{
  header("HTTP/1.0 404 Not Found");
  exit();
}

function httpBadMethod()
{
  header("HTTP/1.0 405 Method Not Allowed");
  exit();
}

function httpInternalError()
{
  header("HTTP/1.0 500 Internal Server Error");
  exit();
}
?>
