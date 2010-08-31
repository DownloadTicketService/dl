#!/usr/bin/env php
<?php
if(get_magic_quotes_runtime())
  set_magic_quotes_runtime(0);
if(!isset($argc)) die("not running from the command line\n");

// data
require_once("../confwrap.php");
require_once("../admfuncs.php");

// initialize the old 0.3 dbs
if(!isset($dbHandler)) $dbHandler = "db4";
$tDbPath = $spoolDir . "/data.db";
$uDbPath = $spoolDir . "/user.db";
$tDb = dba_popen($tDbPath, "r", $dbHandler) or die();
$uDb = dba_popen($uDbPath, "r", $dbHandler) or die();

// initialize the new db connection
$db = new PDO($dsn);
$db->exec('PRAGMA foreign_keys = ON');

// convert user information
echo "converting users ...\n";

$userId = $db->query("SELECT id FROM role WHERE name = 'user'")->fetchColumn();
$adminId = $db->query("SELECT id FROM role WHERE name = 'admin'")->fetchColumn();

for($key = dba_firstkey($uDb); $key; $key = dba_nextkey($uDb))
{
  $DATA = dba_fetch($key, $uDb);
  if($DATA === false) continue;
  $DATA = unserialize($DATA);

  echo " ... $key\n";

  // prepare the SQL
  $sql = "INSERT INTO user (name, pass_md5, role_id) VALUES (";
  $sql .= $db->quote($key);
  $sql .= ", " . (empty($DATA["pass"])?
      'NULL': $db->quote($DATA["pass"]));
  $sql .= ", " . ($DATA["admin"]? $adminId: $userId);
  $sql .= ")";

  $db->exec($sql);
}

$xUsers = array();

for($key = dba_firstkey($tDb); $key; $key = dba_nextkey($tDb))
{
  $DATA = dba_fetch($key, $tDb);
  if($DATA === false) continue;
  $DATA = unserialize($DATA);

  $xUsers[$DATA["user"]] = true;
}

foreach($xUsers as $key => $value)
{
  echo " ... $key\n";

  // prepare the SQL
  $sql = "INSERT INTO user (name, pass_md5, role_id) VALUES (";
  $sql .= $db->quote($key);
  $sql .= ", NULL";
  $sql .= ", $userId";
  $sql .= ")";

  $db->exec($sql);
}

echo "done\n";


// convert ticket information
echo "converting tickets ...\n";

for($key = dba_firstkey($tDb); $key; $key = dba_nextkey($tDb))
{
  $DATA = dba_fetch($key, $tDb);
  if($DATA === false) continue;
  $DATA = unserialize($DATA);

  echo " ... $key\n";

  // prepare the SQL
  $sql = "INSERT INTO ticket (id, user_id, name, path, size, cmt, time"
    . ", downloads, last_stamp, last_time, expire, expire_last, expire_dln"
    . ", notify_email) VALUES (";
  $sql .= $db->quote($key);
  $sql .= ", (SELECT id FROM user WHERE name = " . $db->quote($DATA["user"]) . ")";
  $sql .= ", " . $db->quote($DATA["name"]);
  $sql .= ", " . $db->quote($DATA["path"]);
  $sql .= ", " . $DATA["size"];
  $sql .= ", " . (empty($DATA["cmt"])? 'NULL': $db->quote($DATA["cmt"]));
  $sql .= ", " . $DATA["time"];
  $sql .= ", " . $DATA["downloads"];
  $sql .= ", " . ($DATA["lastTime"] == 0? 'NULL': $DATA["lastTime"]);
  $sql .= ", " . ($DATA["expireLast"] == 0? 'NULL': $DATA["expireLast"]);
  $sql .= ", " . ($DATA["expire"] == 0? 'NULL': $DATA["expire"]);
  $sql .= ", " . ($DATA["expireLast"] == 0 || $DATA["lastTime"] == 0? 'NULL': $DATA["lastTime"] + $DATA["expireLast"]);
  $sql .= ", " . ($DATA["expireDln"] == 0? 'NULL': $DATA["expireDln"]);
  $sql .= ", " . (empty($DATA["email"])? 'NULL': $db->quote($DATA["email"]));
  $sql .= ")";

  $db->exec($sql);
}

echo "done\n";
exit(0);
