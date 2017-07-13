<?php
// dl ticket messages

function msgTicketCreate($DATA, &$subject, &$body)
{
  $subject = sprintf(T_("[dl] download link to %s"), $DATA['name']);
  if(!empty($DATA['cmt']))
    $body .= T_("Ticket comment:") . " " . $DATA['cmt'] . ".\n\n";
  $body .= T_("URL:") . " " . ticketUrl($DATA) . "\n";
  if($DATA['pass_send'] && !empty($DATA['pass']))
    $body .= T_("Password:") . " " . $DATA['pass'] . "\n";
}


function msgTicketDownload($DATA, &$subject, &$body)
{
  global $masterPath;
  $subject = sprintf(T_("[dl] ticket %s download notification"), ticketStr($DATA));
  $body = sprintf(T_("The ticket %s was downloaded by %s from %s"),
      ticketStr($DATA), $_SERVER["REMOTE_ADDR"], $masterPath);
}


function msgTicketPurge($DATA, &$subject, &$body)
{
  global $masterPath;
  $subject = sprintf(T_("[dl] ticket %s purge notification"), ticketStr($DATA));
  $body = sprintf(T_("The ticket %s was purged manually after %d downloads from %s"),
      ticketStr($DATA), $DATA["downloads"], $masterPath);
}


function msgTicketExpire($DATA, &$subject, &$body)
{
  global $masterPath;
  $subject = sprintf(T_("[dl] ticket %s purge notification"), ticketStr($DATA));
  $body = sprintf(T_("The ticket %s expired automatically after %d downloads from %s"),
      ticketStr($DATA), $DATA["downloads"], $masterPath);
}


function msgGrantCreate($DATA, &$subject, &$body)
{
  $subject = T_("[dl] upload grant link");
  if(!empty($DATA['cmt']))
    $body .= T_("Grant comment:") . " " . $DATA['cmt'] . ".\n\n";
  $body .= T_("URL:") . " " . grantUrl($DATA) . "\n";
  if($DATA['pass_send'] && !empty($DATA['pass']))
    $body .= T_("Password:") . " " . $DATA['pass'] . "\n";
}


function msgGrantPurge($DATA, &$subject, &$body)
{
  global $masterPath;
  $subject = sprintf(T_("[dl] grant %s purge notification"), grantStr($DATA));
  $body = sprintf(T_("The grant %s was purged manually from %s"), grantStr($DATA), $masterPath);
}


function msgGrantExpire($DATA, &$subject, &$body)
{
  global $masterPath;
  $subject = sprintf(T_("[dl] grant %s purge notification"), grantStr($DATA));
  $body = sprintf(T_("The grant %s expired automatically from %s"), grantStr($DATA), $masterPath);
}


function msgGrantUse($GRANT, $TICKET, &$subject, &$body)
{
  global $dateFmtShort;
  $subject = sprintf(T_("[dl] download link for grant %s"), grantStr($GRANT));
  $body = sprintf(T_("Your grant %s has been used on %s by %s."),
		  grantStr($GRANT), date($dateFmtShort, $GRANT["time"]),
		  $_SERVER["REMOTE_ADDR"]) . "\n";
  if(!empty($GRANT['cmt']))
    $body .= T_("Grant comment:") . " " . $GRANT['cmt'] . ".\n\n";

  $body .= sprintf(T_("The uploaded file (%s) is now available to be downloaded at:\n"),
		   $TICKET['name']);
  $body .= T_("URL:") . " " . ticketUrl($TICKET) . "\n";
  if($DATA['pass_send'] && !empty($TICKET['pass']))
    $body .= T_("Password:") . " " . $TICKET['pass'] . "\n";
  if(!empty($TICKET['cmt']))
    $body .= T_("Upload comment:") . " " . $TICKET['cmt'] . ".\n";
}
