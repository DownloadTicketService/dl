<?php
// dl ticket messages

function msgTicketCreate($DATA, &$subject, &$body)
{
  $subject = sprintf(T_("[dl] download link to %s"), $DATA['name']);
  $body = (!empty($DATA['cmt'])? $DATA['cmt'] . "\n\n": "");
  if(!isset($DATA['pass']))
    $body .= ticketUrl($DATA);
  else
  {
    $body .= T_("URL:") . " " . ticketUrl($DATA) . "\n"
      . T_("Password:") . " " . $DATA['pass'] . "\n";
  }
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
  $body = (!empty($DATA['cmt'])? $DATA['cmt'] . "\n\n": "");
  if(!isset($DATA['pass']))
    $body .= grantUrl($DATA);
  else
  {
    $body .= T_("URL:") . " " . grantUrl($DATA) . "\n"
      . T_("Password:") . " " . $DATA['pass'] . "\n";
  }
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


function msgGrantUse($GRANT, $DATA, &$subject, &$body)
{
  $subject = sprintf(T_("[dl] download link for grant %s"), grantStr($GRANT));
  $body = sprintf(T_("Your grant %s has been used by %s."
	  . " The uploaded file (%s) is now available to be downloaded at:\n\n"),
      grantStr($GRANT), $_SERVER["REMOTE_ADDR"], $DATA['name']);
  if(!isset($DATA['pass']))
    $body .= ticketUrl($DATA);
  else
  {
    $body .= T_("URL:") . " " . ticketUrl($DATA) . "\n"
      . T_("Password:") . " " . $DATA['pass'] . "\n";
  }
}
