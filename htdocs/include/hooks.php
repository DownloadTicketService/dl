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
    $body = (!isset($DATA['pass'])? $url: (T_("URL:") . " $url\n" .  T_("Password:") . " " . $DATA['pass'] . "\n"));
    mailUTF8($email, sprintf(T_("[dl] download link to %s"),
	humanTicketStr($DATA)), $body, "From: $fromAddr");
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
    mailUTF8($DATA["notify_email"],
	sprintf(T_("[dl] ticket %s download notification"), ticketStr($DATA)),
	sprintf(T_("The ticket %s was downloaded by %s from %s"),
	    humanTicketStr($DATA), $_SERVER["REMOTE_ADDR"], $masterPath),
	"From: $fromAddr");
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

    $reason = ($auto? T_("automatically"): T_("manually"));
    mailUTF8($DATA["notify_email"],
	sprintf(T_("[dl] ticket %s purge notification"), ticketStr($DATA)),
	sprintf(T_("The ticket %s was purged %s after %d downloads from %s"),
	    humanTicketStr($DATA), $reason, $DATA["downloads"], $masterPath),
	"From: $fromAddr");
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
    $body = (!isset($DATA['pass'])? $url: (T_("URL:") . " $url\n" .  T_("Password:") . " " . $DATA['pass'] . "\n"));
    mailUTF8($email, T_("[dl] upload grant link"), $body, "From: $fromAddr");
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

    $reason = ($auto? T_("automatically"): T_("manually"));
    mailUTF8($DATA["notify_email"],
	sprintf(T_("[dl] grant %s purge notification"), grantStr($DATA)),
	sprintf(T_("The grant %s was purged %s from %s"),
	    grantStr($DATA), $reason, $masterPath),
	"From: $fromAddr");
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
    mailUTF8($GRANT["notify_email"],
	sprintf(T_("[dl] download link for grant %s"), grantStr($GRANT)),
	sprintf(T_("Your grant %s has been used by %s."
		. " The uploaded file is now available to be"
		. " downloaded at %s"),
	    grantStr($GRANT), $_SERVER["REMOTE_ADDR"], ticketUrl($DATA)),
	"From: $fromAddr");
  }
}

?>
