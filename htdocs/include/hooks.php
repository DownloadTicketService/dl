<?php
// dl ticket event hooks
require_once("msg.php");


function onTicketCreate($DATA)
{
  global $fromAddr;

  // log
  $type = (!$DATA["expire"]? "permanent": "temporary");
  logTicketEvent($DATA, "$type ticket created");

  // send emails to recipient
  foreach(getEMailAddrs($DATA['sent_email']) as $email)
  {
    logTicketEvent($DATA, "sending link to $email");

    // please note that address splitting is performed to avoid
    // disclosing the recipient list (not normally needed)
    withLocale($DATA['locale'], 'msgTicketCreate', array($DATA, &$subject, &$body));
    mailUTF8($email, $subject, $body, "From: $fromAddr");
  }
}


function onTicketUpdate($DATA)
{
  // stub
}


function onTicketDownload($DATA)
{
  global $fromAddr;

  // log
  logTicketEvent($DATA, "downloaded by " . $_SERVER["REMOTE_ADDR"]);

  // notify if request
  if(!empty($DATA["notify_email"]))
  {
    logTicketEvent($DATA, "sending notification to " . $DATA["notify_email"]);
    withLocale($DATA['locale'], 'msgTicketDownload', array($DATA, &$subject, &$body));
    mailUTF8($DATA["notify_email"], $subject, $body, "From: $fromAddr");
  }
}


function onTicketPurge($DATA, $auto)
{
  global $fromAddr;

  // log
  $reason = ($auto? "automatically": "manually");
  logTicketEvent($DATA, "purged $reason after "
      . $DATA["downloads"] . " downloads");

  // notify if requested
  if(!empty($DATA["notify_email"]))
  {
    logTicketEvent($DATA, "sending notification to " . $DATA["notify_email"]);
    withLocale($DATA['locale'],
	($auto? 'msgTicketExpire': 'msgTicketPurge'),
	array($DATA, &$subject, &$body));
    mailUTF8($DATA["notify_email"], $subject, $body, "From: $fromAddr");
  }
}


function onGrantCreate($DATA)
{
  global $fromAddr;

  // log
  $type = (!$DATA["expire"]? "permanent": "temporary");
  logGrantEvent($DATA, "$type grant created");

  // send emails to recipient
  foreach(getEMailAddrs($DATA['sent_email']) as $email)
  {
    logGrantEvent($DATA, "sending link to $email");

    // please note that address splitting is performed to avoid
    // disclosing the recipient list (not normally needed)
    withLocale($DATA['locale'], 'msgGrantCreate', array($DATA, &$subject, &$body));
    mailUTF8($email, $subject, $body, "From: $fromAddr");
  }
}


function onGrantPurge($DATA, $auto)
{
  global $fromAddr;

  // log
  $reason = ($auto? "automatically": "manually");
  logGrantEvent($DATA, "purged $reason");

  // notify if requested
  if(!empty($DATA["notify_email"]))
  {
    logGrantEvent($DATA, "sending notification to " . $DATA["notify_email"]);
    withLocale($DATA['locale'],
	($auto? 'msgGrantExpire': 'msgGrantPurge'),
	array($DATA, &$subject, &$body));
    mailUTF8($DATA["notify_email"], $subject, $body, "From: $fromAddr");
  }
}


function onGrantUse($GRANT, $DATA)
{
  global $fromAddr;

  // log
  logGrantEvent($GRANT, "genenerated ticket " . $DATA['id']
      . " by " . $_SERVER["REMOTE_ADDR"]);

  // notify
  if(!empty($GRANT['notify_email']))
  {
    logGrantEvent($GRANT, "sending link to " . $GRANT["notify_email"]);
    withLocale($GRANT['locale'], 'msgGrantUse', array($GRANT, $DATA, &$subject, &$body));
    mailUTF8($GRANT["notify_email"], $subject, $body, "From: $fromAddr");
  }
}
