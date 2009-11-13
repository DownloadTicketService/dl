<?php
// dl ticket event hooks

function onCreate($DATA)
{
  global $fromAddr, $masterPath;

  // log
  $type = (!$DATA["expire"]? "permanent": "temporary");
  logTicketEvent($DATA, "$type ticket created");

  // send emails to recipient
  foreach(getEMailAddrs($DATA['st']) as $email)
  {
    logTicketEvent($DATA, "sending link to $email");

    // please note that address splitting is performed to avoid
    // disclosing the recipient list (not needed elsewere)
    $url = ticketUrl($DATA);
    $body = (!isset($DATA['pass'])? $url: "URL: $url\nPassword: " . $DATA['pass']);
    mail($email, "download link to " . humanTicketStr($DATA),
	$body, "From: $fromAddr");
  }
}


function onDownload($DATA)
{
  global $fromAddr, $masterPath;

  // log
  logTicketEvent($DATA, "downloaded by " . $_SERVER["REMOTE_ADDR"]);

  // notify if request
  if(!empty($DATA["notify_email"]))
  {
    logTicketEvent($DATA, "sending notification to " . $DATA["notify_email"]);
    mail($DATA["notify_email"], "[dl] " . ticketStr($DATA) . " download notification",
	humanTicketStr($DATA) . " was downloaded by " . $_SERVER["REMOTE_ADDR"]
	. " from $masterPath\n", "From: $fromAddr");
  }
}


function onPurge($DATA, $auto)
{
  global $fromAddr, $masterPath;

  // log
  $reason = ($auto? "automatically": "manually");
  logTicketEvent($DATA, "purged $reason after "
      . $DATA["downloads"] . " downloads");

  // notify if requested
  if(!empty($DATA["notify_email"]))
  {
    logTicketEvent($DATA, "sending notification to " . $DATA["notify_email"]);
    mail($DATA["notify_email"], "[dl] " . ticketStr($DATA) . " purge notification",
	humanTicketStr($DATA) . " was purged after " . $DATA["downloads"]
	. " downloads from $masterPath\n", "From: $fromAddr");
  }
}

?>
