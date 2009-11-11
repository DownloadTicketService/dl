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
$sql = "SELECT * FROM tickets WHERE expire < " . time();
$sql .= " OR expire_last + last_time < " . time();
$sql .= " OR expire_dln <= downloads";
foreach($db->query($sql) as $DATA)
  purgeDl($DATA);

// start the session
session_name($sessionName);
session_start();

?>
