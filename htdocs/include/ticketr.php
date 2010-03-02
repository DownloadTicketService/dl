<?php
// download a ticket

// fetch the ticket id
if(!isset($_SERVER["PATH_INFO"]))
{
  header("HTTP/1.0 400 Bad Request");
  exit();
}

list(, $id) = explode("/", $_SERVER["PATH_INFO"]);

// try to fetch the id
$sql = "SELECT * FROM ticket WHERE id = " . $db->quote($id);
$DATA = $db->query($sql)->fetch();
if($DATA === false || isTicketExpired($DATA))
{
  header("HTTP/1.0 404 Not Found");
  exit();
}

// check for password
if(isset($DATA['pass_md5']) && !isset($_SESSION['t'][$id]))
{
  header("HTTP/1.0 400 Bad Request");
  exit();
}

// open the file first
$fd = fopen($DATA["path"], "r");
if($fd === false)
{
  header("HTTP/1.0 500 Internal Server Error");
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

// update the record for the next query
$sql = "UPDATE ticket SET last_stamp = " . time()
  . ", expire_last = " . time() . " + last_time"
  . ", downloads = downloads + 1 WHERE id = " . $db->quote($id);
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

// trigger download hooks
if($last) onTicketDownload($DATA);

?>
