<?php
// download a ticket

// fetch the ticket id
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

// update range parameters
if(!empty($_SERVER["HTTP_RANGE"]))
  preg_match("/^bytes=(\d*)-(\d*)/", $_SERVER["HTTP_RANGE"], $range);
if(empty($range[1]) || $range[1] < 0 || $range[1] >= $DATA["size"])
  $range[1] = 0;
if(empty($rage[2]) || $range[2] < $range[1] || $range[2] >= $DATA["size"])
  $range[2] = $DATA["size"] - 1;
$size = max(0, $range[2] - $range[1] + 1);
$complete = ($size == $DATA["size"]);
$last = ($range[2] == $DATA["size"] - 1);

// update the record
$DATA["lastTime"] = time();
if($last) $DATA["downloads"]++;
dba_replace($id, serialize($DATA), $tDb);

// send the file
header("ETag: $id");
header("Pragma: private");
header("Cache-Control: cache");
header("Accept-Ranges: bytes");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=" . urlencode($DATA["name"]));
if(!$complete)
{
  header("HTTP/1.1 206 Partial Content");
  header("Content-Range: bytes $range[1]-$range[2]/" . $DATA["size"]);
}
header("Content-Length: $size");

// contents
$left = $size;
fseek($fd, $range[1]);
while($left)
{
  $data = fread($fd, 16384);
  $left -= strlen($data);
  print($data);
  flush();
}
fclose($fd);

// notify if requested
if(!empty($DATA["email"]) && $last)
{
  mail($DATA["email"], "[dl] $id download notification",
      $id . " (" . $DATA["name"] . ") was downloaded by " .
      $_SERVER["REMOTE_ADDR"] . " from $masterPath\n",
      "From: $fromAddr");
}
?>
