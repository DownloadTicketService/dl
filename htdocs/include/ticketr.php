<?php
// download a ticket
require_once("ticketfuncs.php");

// fetch the ticket id
if(!isset($_SERVER["PATH_INFO"]))
  httpBadRequest();

$id = false;
if(preg_match("/^\/([^\/]+)/", $_SERVER["PATH_INFO"], $tmp)) $id = $tmp[1];
if($id === false || !isTicketId($id))
  httpNotFound();

// try to fetch the id
$sql = "SELECT * FROM ticket WHERE id = " . $db->quote($id);
$DATA = $db->query($sql)->fetch();
if($DATA === false || isTicketExpired($DATA))
  httpNotFound();

// check for password
if(hasPassHash($DATA) && !isset($_SESSION['t'][$id]))
  httpBadRequest();

// open the file first
$fd = fopen($DATA["path"], "r");
if($fd === false)
  httpInternalError();

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

// update the record for the next query
$now = time();
$sql = "UPDATE ticket SET last_stamp = $now"
  . " WHERE id = " . $db->quote($id);
$db->exec($sql);

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
session_write_close();
ob_end_flush();

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

if($last && !connection_aborted())
{
  session_start();

  ++$DATA["downloads"];

  // set default locale for notifications
  switchLocale($defLocale);

  // trigger download hooks
  onTicketDownload($DATA);

  // check for validity after download
  if(isTicketExpired($DATA))
    ticketPurge($DATA);
  else
  {
    // update download count
    $now = time();
    $sql = "UPDATE ticket SET last_stamp = $now"
      . ", downloads = downloads + 1 WHERE id = " . $db->quote($id);
    $db->exec($sql);
  }

  // kill the session ASAP
  if($auth === false)
    session_destroy();
}

?>
