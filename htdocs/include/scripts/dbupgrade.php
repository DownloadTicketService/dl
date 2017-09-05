#!/usr/bin/env php
<?php
if(!isset($argc)) die("not running from the command line\n");

// data
require_once("../prelude.php");
require_once("confwrap.php");
require_once("admfuncs.php");
require_once("dbfuncs.php");

// initialize the db connection
connectDB(false);

// fetch current db release
$sql = "SELECT \"value\" FROM \"config\" WHERE \"name\" = 'version'";
$version = @$db->query($sql)->fetchColumn();

if(!$version || version_compare($version, "0.4", "<"))
{
  echo "upgrading 0.3 => 0.4 ...\n";

  $db->exec("CREATE TABLE \"config\" (\"name\" VARCHAR PRIMARY KEY, \"value\" VARCHAR)");
  $db->exec("INSERT INTO \"config\" VALUES('version', '0.4')");

  $version = "0.4";
}

if(version_compare($version, "0.10", "<"))
{
  echo "upgrading 0.4 => 0.10 ...\n";

  $db->exec("ALTER TABLE \"ticket\" ADD \"sent_email\" VARCHAR(1023)");
  $db->exec("ALTER TABLE \"ticket\" ADD \"locale\" VARCHAR(255)");
  if($db->driver() != "sqlite")
  {
    // not supported by sqlite, it will leave the column
    $db->exec("ALTER TABLE \"ticket\" DROP \"expire_last\"");
  }
  $db->exec("ALTER TABLE \"grant\" ADD \"sent_email\" VARCHAR(1023)");
  $db->exec("ALTER TABLE \"grant\" ADD \"locale\" VARCHAR(255)");
  $db->exec("UPDATE \"config\" SET \"value\" = '0.10' WHERE \"name\" = 'version'");

  $version = "0.10";
}

if(version_compare($version, "0.11", "<"))
{
  echo "upgrading 0.10 => 0.11 ...\n";

  $db->exec("ALTER TABLE \"user\" ADD \"pass_ph\" VARCHAR(60)");
  $db->exec("ALTER TABLE \"ticket\" ADD \"pass_ph\" VARCHAR(60)");
  $db->exec("ALTER TABLE \"grant\" ADD \"pass_ph\" VARCHAR(60)");
  $db->exec("UPDATE \"config\" SET \"value\" = '0.11' WHERE \"name\" = 'version'");

  $version = "0.11";
}

if(version_compare($version, "0.12", "<"))
{
  echo "upgrading 0.11 => 0.12 ...\n";

  $db->exec("ALTER TABLE \"user\" ADD \"email\" VARCHAR(255)");
  $db->exec("UPDATE \"config\" SET \"value\" = '0.12' WHERE \"name\" = 'version'");

  $version = "0.12";
}

if(version_compare($version, "0.18", "<"))
{
  echo "upgrading 0.12 => 0.18 ...\n";

  // unused columns
  if($db->driver() != "sqlite")
  {
    // not supported by sqlite, it will leave the column
    $db->exec("ALTER TABLE \"grant\" DROP \"downloads\"");
  }

  // shift expire times to be relative
  $db->exec("UPDATE \"ticket\" SET \"expire\" = expire - time");
  $db->exec("UPDATE \"grant\" SET \"expire\" = expire - time");
  $db->exec("UPDATE \"grant\" SET \"grant_expire\" = grant_expire - time");

  // track grant usage
  $db->exec("ALTER TABLE \"ticket\" ADD \"from_grant\" CHAR(32)");

  // password policy
  $db->exec("ALTER TABLE \"ticket\" ADD \"pass_send\" BOOLEAN NOT NULL DEFAULT 0");
  $db->exec("ALTER TABLE \"grant\" ADD \"pass_send\" BOOLEAN NOT NULL DEFAULT 0");

  // multiple grant re-use
  $db->exec("ALTER TABLE \"grant\" ADD \"grant_last_time\" INTEGER");
  $db->exec("ALTER TABLE \"grant\" ADD \"grant_expire_uln\" INTEGER");
  $db->exec("ALTER TABLE \"grant\" ADD \"uploads\" INTEGER NOT NULL DEFAULT 0");
  $db->exec("ALTER TABLE \"grant\" ADD \"last_stamp\" INTEGER");
  $db->exec("DROP INDEX \"i_grant\" ON \"grant\"");
  $db->exec("CREATE INDEX \"i_grant\" on \"grant\" ( \"grant_expire\", \"grant_expire_uln\", \"uploads\" )");

  // match previous defaults
  $db->exec("UPDATE \"ticket\" SET \"pass_send\" = 1");
  $db->exec("UPDATE \"grant\" SET \"pass_send\" = 1");
  $db->exec("UPDATE \"grant\" SET \"grant_expire_uln\" = 1");

  // Allow size >2GB
  switch($db->driver())
  {
  case "mysql":
    $db->exec("ALTER TABLE \"ticket\" MODIFY \"size\" BIGINT NOT NULL");
    break;

  case "pgsql":
    $db->exec("ALTER TABLE \"ticket\" ALTER COLUMN \"size\" TYPE BIGINT");
    break;
  }

  $db->exec("UPDATE \"config\" SET \"value\" = '0.18' WHERE \"name\" = 'version'");
  $version = "0.18";
}

echo "done\n";
exit(0);
