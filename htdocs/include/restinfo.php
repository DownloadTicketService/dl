<?php
// REST "info" request

function info($msg, $params = null)
{
  global $dlVersion, $bannerUrl, $masterPath, $rPath, $iMaxSize;
  global $defaultTotalDays, $defaultLastDl, $defaultMaxDl;

  return array
  (
    'version'    => $dlVersion,
    'url'        => $bannerUrl,
    'masterpath' => $masterPath,
    'restpath'   => $rPath,
    'maxsize'    => $iMaxSize,
    'defaults'   => array
    (
      'totaldays' => $defaultTotalDays,
      'lastdl' => $defaultLastDl,
      'maxdl' => $defaultMaxDl,
    ),
  );
}

?>
