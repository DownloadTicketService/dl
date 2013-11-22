#!/usr/bin/env php
<?php
if(!isset($argc)) die("not running from the command line\n");

// data
require_once("../prelude.php");
require_once("confwrap.php");
require_once("admfuncs.php");

// initialize the db connection
$db = new PDO($dsn);
$db->exec('PRAGMA foreign_keys = ON');

// fetch current db release
$sql = "SELECT value FROM config WHERE name = 'version'";
$version = @$db->query($sql)->fetchColumn();

$tDbPath = $spoolDir . "/data.db";
$uDbPath = $spoolDir . "/user.db";
if(!$version && file_exists($tDbPath) && file_exists($uDbPath))
{
  echo "upgrading <0.3 => 0.3 ...\n";

  // initialize the old 0.3 dbs
  if(!isset($dbHandler)) $dbHandler = "db4";
  $tDb = dba_popen($tDbPath, "r", $dbHandler) or die();
  $uDb = dba_popen($uDbPath, "r", $dbHandler) or die();

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

  echo "warning: please remember to move away $tDbPath/$uDbPath\n";
  $version = "0.3";
}

if(!$version || version_compare($version, "0.4", "<"))
{
  echo "upgrading 0.3 => 0.4 ...\n";

  $db->exec("CREATE TABLE config (name VARCHAR PRIMARY KEY, value VARCHAR)");
  $db->exec("INSERT INTO config VALUES('version', '0.4')");

  $version = "0.4";
}

if(version_compare($version, "0.10", "<"))
{
  echo "upgrading 0.4 => 0.10 ...\n";

  $db->exec("ALTER TABLE ticket ADD sent_email VARCHAR");
  $db->exec("ALTER TABLE ticket ADD locale VARCHAR");
  $db->exec("ALTER TABLE ticket DROP expire_last"); # not supported by sqlite, it will leave the column
  $db->exec("ALTER TABLE grant ADD sent_email VARCHAR");
  $db->exec("ALTER TABLE grant ADD locale VARCHAR");
  $db->exec("UPDATE config SET value = '0.10' WHERE name = 'version'");

  $version = "0.10";
}

if(version_compare($version, "0.11", "<"))
{
  echo "upgrading 0.10 => 0.11 ...\n";

  $db->exec("ALTER TABLE user ADD pass_ph VARCHAR");
  $db->exec("ALTER TABLE ticket ADD pass_ph VARCHAR");
  $db->exec("ALTER TABLE grant ADD pass_ph VARCHAR");
  $db->exec("UPDATE config SET value = '0.11' WHERE name = 'version'");

  $version = "0.11";
}

if(version_compare($version, "0.12", "<"))
{
  echo "upgrading 0.11 => 0.12 ...\n";

  $db->exec("ALTER TABLE user ADD email VARCHAR(255)");
  $db->exec("UPDATE config SET value = '0.12' WHERE name = 'version'");

  $version = "0.12";
}

echo "done\n";
exit(0);
