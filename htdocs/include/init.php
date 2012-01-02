<?php
// basic initialization
require_once("prelude.php");
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

// set the initial default locale
$locale = $defLocale;
switchLocale($locale);
?>
