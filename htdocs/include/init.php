<?php
// initialize the spool directory and authorization
set_magic_quotes_runtime(0);

// data
require_once("confwrap.php");

// initialize the dbs
$dbMode = (version_compare(PHP_VERSION, "4.3.5", "<")? "w": "c");
$tDb = dba_popen($tDbPath, $dbMode, $dbHandler) or die();
$uDb = dba_popen($uDbPath, $dbMode, $dbHandler) or die();

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

?>
