<?php
// basic initialization
require_once("prelude.php");
require_once("confwrap.php");
require_once("lang.php");

// check data dirs
if(!posix_access($spoolDir, POSIX_R_OK | POSIX_W_OK))
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
  $db = new PDO($dsn);
  $db->exec('PRAGMA foreign_keys = ON');
}
catch(PDOException $e)
{
  die("cannot initialize database");
}

// check schema version
$sql = "SELECT value FROM config WHERE name = 'version'";
if(!($query = $db->query($sql)))
  die("cannot initialize database");
$version = $query->fetchColumn();
if(version_compare($version, $dlVersion, "!="))
  die("database requires schema upgrade");

// set the initial default locale
$locale = $defLocale;
switchLocale($locale);
?>
