<?php
// initialize the spool directory and authorization

// setup the runtime
if(get_magic_quotes_runtime())
  set_magic_quotes_runtime(0);
ob_start();

// data
require_once("confwrap.php");
require_once("lang.php");

// initialize the db
$db = new PDO($dsn);
$db->exec('PRAGMA foreign_keys = ON');

// initialize logging
if($useSysLog)
   openlog($logFile, 0, LOG_LOCAL0);
elseif(!empty($logFile))
  $logFd = fopen($logFile, "at");

// set default locale for notifications
switchLocale($defLocale);

// expire tickets before serving any request
if(($gcProbability === 1.)
|| (mt_rand() / mt_getrandmax() < $gcProbability))
{
  $now = time();

  $sql = "SELECT * FROM ticket WHERE expire < $now";
  $sql .= " OR expire_last < $now";
  $sql .= " OR expire_dln <= downloads";
  if($gcLimit) $sql .= " LIMIT $gcLimit";
  foreach($db->query($sql)->fetchAll() as $DATA)
    ticketPurge($DATA);
  
  // expire grants
  $sql = "SELECT * FROM grant WHERE grant_expire < $now";
  if($gcLimit) $sql .= " LIMIT $gcLimit";
  foreach($db->query($sql)->fetchAll() as $DATA)
    grantPurge($DATA);
}

// start the session
session_name($sessionName);
session_start();
$auth = &$_SESSION["auth"];

// set session's locale
$locale = &$_SESSION["locale"];
$locale = detectLocale($locale);
switchLocale($locale);

?>
