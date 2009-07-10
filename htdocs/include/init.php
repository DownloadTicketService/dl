<?php
// initialize the spool directory and authorization
set_magic_quotes_runtime(0);

// data
require_once("config.php");
require_once("funcs.php");

// derived data
$iMaxSize = returnBytes($maxSize);
$hMaxSize = round($iMaxSize / 1048576, 3) . "MB";
$tDbPath = $spoolDir . "/data.db";
$uDbPath = $spoolDir . "/user.db";
$Path = $spoolDir . "/data.db";
$dataDir = $spoolDir . "/data";

// initialize the dbs
$dbMode = (version_compare(PHP_VERSION, "4.3.5", "<")? "w": "c");
$tDb = dba_popen($tDbPath, $dbMode, $dbHandler);
$uDb = dba_popen($uDbPath, $dbMode, $dbHandler);

// some utilities
function purgeDl($key, $DATA)
{
  global $tDb, $fromAddr, $masterPath;

  if(dba_delete($key, $tDb))
  {
    unlink($DATA["path"]);

    // notify if requested
    if(!empty($DATA["email"]))
    {
      mail($DATA["email"], "[dl] $key purge notification",
	  $key . " (" . $DATA["name"] . ") was purged after " .
	  $DATA["downloads"] . " downloads from $masterPath\n",
	  "From: $fromAddr");
    }
  }
}


// expire tickets
for($key = dba_firstkey($tDb); $key; $key = dba_nextkey($tDb))
{
  $DATA = dba_fetch($key, $tDb);
  if($DATA === false) continue;
  $DATA = unserialize($DATA);
  if(
      ($DATA["expire"] && $DATA["expire"] < time()) ||
      ($DATA["expireLast"] && $DATA["lastTime"] &&
	  ($DATA["expireLast"] + $DATA["lastTime"]) < time()) ||
      ($DATA["expireDln"] && $DATA["downloads"] >= $DATA["expireDln"])
     )
    purgeDl($key, $DATA);
}

// authorization
session_start();
$auth = (isset($_SESSION["auth"])? $_SESSION["auth"]: false);
if(isset($_REQUEST["p"]))
  $auth = $_SESSION["auth"] = ($_REQUEST["p"] == $masterPass);
?>
