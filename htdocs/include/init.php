<?php
// initialize the spool directory and authorization
set_magic_quotes_runtime(0);

// data
require_once("confwrap.php");

// initialize the db
$db = new PDO($dsn);
$db->exec('PRAGMA foreign_keys = ON');

// initialize logging
if($useSysLog)
   openlog($logFile, 0, LOG_LOCAL0);
elseif(!empty($logFile))
  $logFd = fopen($logFile, "at");

// expire tickets before serving any request
$sql = "SELECT * FROM ticket WHERE expire < " . time();
$sql .= " OR expire_last < " . time();
$sql .= " OR expire_dln <= downloads";
foreach($db->query($sql) as $DATA)
  ticketPurge($DATA);

// expire grants
$sql = "SELECT * FROM grant WHERE grant_expire < " . time();
foreach($db->query($sql) as $DATA)
  grantPurge($DATA);

// start the session
session_name($sessionName);
session_start();
$auth = &$_SESSION["auth"];

// language support
$_SESSION["locale"] = $locale;
$locale = &$_SESSION["locale"];
setlocale(LC_ALL, $locale . ".utf8");
bindtextdomain('messages', 'include/locale');
textdomain('messages');

?>
