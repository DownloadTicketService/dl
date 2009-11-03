<?php
// dl ticket event hooks

function onCreate($DATA)
{
  logEvent("(" . $DATA["name"] . "): " . $DATA["cmt"] . ": "
      . (!$DATA["expire"]? "permanent": "temporary") . " ticket created");
}


function onDownload($DATA)
{
  global $fromAddr;

  // notify if request
  if(!empty($DATA["email"]))
    mail($DATA["email"], "[dl] " . $DATA["id"] . " download notification",
	$DATA["id"] . " (" . $DATA["name"] . ") was downloaded by "
	. $_SERVER["REMOTE_ADDR"] . " from $masterPath\n",
	"From: $fromAddr");

  // log
  logEvent("(" . $DATA["name"] . "): "
      . $DATA["cmt"] . ": downloaded by "
      . $_SERVER["REMOTE_ADDR"]);
}


function onPurge($DATA, $auto)
{
  global $fromAddr, $masterPath;

  // notify if requested
  if(!empty($DATA["email"]))
  {
    mail($DATA["email"], "[dl] " . $DATA["id"] . " purge notification",
	$DATA["id"] . " (" . $DATA["name"] . ") was purged after "
	. $DATA["downloads"] . " downloads from $masterPath\n",
	"From: $fromAddr");
  }

  // log
  $reason = ($auto? "automatically": "manually");
  logEvent("(" . $DATA["name"] . "): "
      . $DATA["cmt"] . ": purged $reason after "
      . $DATA["downloads"] . " downloads");
}

?>
