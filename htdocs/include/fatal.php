<?php
// some helpers for fatal errors
function httpBadRequest()
{
  header("HTTP/1.0 400 Bad Request");
  exit();
}

function httpUnauthorized()
{
  header('HTTP/1.0 401 Unauthorized');
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
