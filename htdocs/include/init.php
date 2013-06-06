<?php
// basic initialization
require_once("prelude.php");
require_once("confwrap.php");
require_once("lang.php");

// check data dirs
if(!is_readable($spoolDir) || !is_writable($spoolDir))
  die("cannot access spool directory");
if(!file_exists($dataDir))
{
  if(!mkdir($dataDir))
    die("cannot initialize data directory");
}

// initialize logging
if($useSysLog)
  $ret = openlog($logFile, 0, LOG_LOCAL0);
elseif(!empty($logFile))
  $ret = $logFd = fopen($logFile, "at");
if(@$ret === false)
  die("cannot initialize logging");

// initialize the db
try
{
  $db = new PDO($dsn, $dbUser, $dbPassword);
  $db->exec('PRAGMA foreign_keys = ON');
}
catch(PDOException $e)
{
  die("cannot initialize database");
}

// check schema version
$sql = "SELECT value FROM config WHERE name = 'version'";
if(!($q = $db->query($sql)))
  die("cannot initialize database");
$version = $q->fetchColumn();
if(version_compare($version, $dlVersion, "!="))
  die("database requires schema upgrade");
unset($q);

// set the initial default locale
$locale = $defLocale;
switchLocale($locale);
?>
