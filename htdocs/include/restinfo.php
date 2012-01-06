<?php
// REST "info" request

function info($msg, $params = null)
{
  global $dlVersion, $bannerUrl, $masterPath, $rPath, $iMaxSize;
  global $defaultTicketTotalDays, $defaultTicketLastDl, $defaultTicketMaxDl;
  global $defaultGrantTotalDays;

  return array(false, array
  (
    'version'    => $dlVersion,
    'url'        => $bannerUrl,
    'masterpath' => $masterPath,
    'maxsize'    => $iMaxSize,
    'defaults'   => array
    (
      'gn'  => $defaultGrantTotalDays,
      'dn'  => $defaultTicketTotalDays,
      'hra' => $defaultTicketLastDl,
      'dln' => $defaultTicketMaxDl,
    ),
  ));
}

?>
