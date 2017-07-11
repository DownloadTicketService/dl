<?php
// REST "info" request

function info($msg, $params = null)
{
  global $dlVersion, $bannerUrl, $masterPath, $rPath, $iMaxSize, $defaults;

  return array(false, array
  (
    'version'    => $dlVersion,
    'url'        => $bannerUrl,
    'masterpath' => $masterPath,
    'maxsize'    => $iMaxSize,
    'defaults'   => array
    (
      'grant'    => array
      (
        'total'  => $defaults['grant']['total'],
        'lastul'  => $defaults['grant']['lastul'],
        'maxul'  => $defaults['grant']['maxul'],
      ),
      'ticket'   => array
      (
        'total'  => $defaults['ticket']['total'],
        'lastdl' => $defaults['ticket']['lastdl'],
        'maxdl'  => $defaults['ticket']['maxdl'],
      ),
    ),
  ));
}
