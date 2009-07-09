<?php
// download a ticket

// fetch the ticket it
if(!isset($_SERVER["PATH_INFO"]))
{
  include("failed.php");
  exit();
}

list(, $id) = explode("/", $_SERVER["PATH_INFO"]);

// try to fetch the id
$DATA = dba_fetch($id, $tDb);
if($DATA === false)
{
  header("HTTP/1.0 404 Not Found");
  exit();
}
$DATA = unserialize($DATA);

// open the file first
$fd = fopen($DATA["path"], "r");
if($fd === false)
{
  include("failed.php");
  exit();
}

// update the record
$DATA["lastTime"] = time();
$DATA["downloads"]++;
dba_replace($id, serialize($DATA), $tDb);

// send the file
header("Pragma: private");
header("Cache-Control: cache");
// not yet: header("Accept-Ranges: bytes");
header("Content-Type: application/octet-stream");
header("Content-Length: " . $DATA["size"]);

$left = $DATA["size"];
while($left)
{
  $data = fread($fd, 16384);
  $left -= strlen($data);
  print($data);
  flush();
}
fclose($fd);

// notify if requested
if(!empty($DATA["email"]))
{
  mail($DATA["email"], "[dl] $id download notification",
      $id . " (" . $DATA["name"] . ") was downloaded by " .
      $_SERVER["REMOTE_ADDR"] . " from $masterPath\n",
      "From: $fromAddr");
}
?>
