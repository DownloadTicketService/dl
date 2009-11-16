<?php
// dl ticket event hooks

function onTicketCreate($DATA)
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
    // disclosing the recipient list (not normally needed)
    $url = ticketUrl($DATA);
    $body = (!isset($DATA['pass'])? $url: "URL: $url\nPassword: " . $DATA['pass']);
    mail($email, "[dl] download link to " . humanTicketStr($DATA),
	$body, "From: $fromAddr");
  }
}


function onTicketDownload($DATA)
{
  global $fromAddr, $masterPath;

  // log
  logTicketEvent($DATA, "downloaded by " . $_SERVER["REMOTE_ADDR"]);

  // notify if request
  if(!empty($DATA["notify_email"]))
  {
    logTicketEvent($DATA, "sending notification to " . $DATA["notify_email"]);
    mail($DATA["notify_email"], "[dl] ticket " . ticketStr($DATA)
	. " download notification", "The ticket " . humanTicketStr($DATA)
	. " was downloaded by " . $_SERVER["REMOTE_ADDR"]
	. " from $masterPath\n", "From: $fromAddr");
  }
}


function onTicketPurge($DATA, $auto)
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
    mail($DATA["notify_email"], "[dl] ticket " . ticketStr($DATA)
	. " purge notification", "The ticket " . humanTicketStr($DATA)
	. " was purged $reason after " . $DATA["downloads"]
	. " downloads from $masterPath\n", "From: $fromAddr");
  }
}


function onGrantCreate($DATA)
{
  global $fromAddr, $masterPath;

  // log
  $type = (!$DATA["expire"]? "permanent": "temporary");
  logGrantEvent($DATA, "$type grant created");

  // send emails to recipient
  foreach(getEMailAddrs($DATA['st']) as $email)
  {
    logGrantEvent($DATA, "sending link to $email");

    // please note that address splitting is performed to avoid
    // disclosing the recipient list (not normally needed)
    $url = grantUrl($DATA);
    $body = (!isset($DATA['pass'])? $url: "URL: $url\nPassword: " . $DATA['pass']);
    mail($email, "[dl] upload grant link", $body, "From: $fromAddr");
  }
}


function onGrantPurge($DATA, $auto)
{
  global $fromAddr, $masterPath;

  // log
  $reason = ($auto? "automatically": "manually");
  logGrantEvent($DATA, "purged $reason");

  // notify if requested
  if(!empty($DATA["notify_email"]))
  {
    logGrantEvent($DATA, "sending notification to " . $DATA["notify_email"]);
    mail($DATA["notify_email"], "[dl] grant " . grantStr($DATA)
	. " purge notification", "The grant " . grantStr($DATA)
	. " was purged $reason from $masterPath\n", "From: $fromAddr");
  }
}


function onGrantUse($GRANT, $DATA)
{
  global $fromAddr, $masterPath;

  // log
  logGrantEvent($GRANT, "genenerated ticket " . $DATA['id']
      . " by " . $_SERVER["REMOTE_ADDR"]);

  // notify
  if(!empty($GRANT['notify_email']))
  {
    logGrantEvent($GRANT, "sending link to " . $GRANT["notify_email"]);
    mail($GRANT["notify_email"], "[dl] download link for grant "
	. grantStr($GRANT), 'Your grant ' . $GRANT['id']
	. ' has been used by ' . $_SERVER["REMOTE_ADDR"]
	. '. The uploaded file is now available to be downloaded at '
	. ticketUrl($DATA), "From: $fromAddr");
  }
}

?>
