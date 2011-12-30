<?php
// some helpers for fatal errors
function httpBadRequest()
{
  header("HTTP/1.0 400 Bad Request");
  exit();
}

function httpNotFound()
{
  header("HTTP/1.0 404 Not Found");
  exit();
}

function httpInternalError()
{
  header("HTTP/1.0 500 Internal Server Error");
  exit();
}
?>