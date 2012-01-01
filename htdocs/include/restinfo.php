<?php
// REST "info" request

function info($msg, $params = null)
{
  global $dlVersion, $bannerUrl, $masterPath, $rPath, $iMaxSize;
  global $defaultTotalDays, $defaultLastDl, $defaultMaxDl;

  return array(false, array
  (
    'version'    => $dlVersion,
    'url'        => $bannerUrl,
    'masterpath' => $masterPath,
    'maxsize'    => $iMaxSize,
    'defaults'   => array
    (
      'dn'  => $defaultTotalDays,
      'hra' => $defaultLastDl,
      'dln' => $defaultMaxDl,
    ),
  ));
}

?>
