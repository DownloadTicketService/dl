#!/usr/bin/env php
<?php
if(get_magic_quotes_runtime())
  set_magic_quotes_runtime(0);
if(!isset($argc)) die("not running from the command line\n");

// data
require_once("../confwrap.php");

// initialize the db
$db = new PDO($dsn);
$db->exec('PRAGMA foreign_keys = ON');

// parse the command line
if($argc < 2 || $argv[1] == 'help')
{
  echo(<<<EOD
Usage: $argv[0] command [args]

  help               : this help
  list               : list all users and their administrator status
  add user adm [pass]: add "user", setting its administrator status with
                       "adm" ("true" or "false") and the optional
                       password "pass".
  rst user adm [pass]: reset the admin status or password for "user".
  passwd user [pass] : just reset the password for "user".
  rm user            : remove "user"

EOD
);
  exit(2);
}

if($argv[1] == 'list')
{
  echo "#user\tadm\n";

  $sql = "SELECT u.name, admin FROM user u LEFT JOIN role r ON r.id = u.role_id";
  foreach($db->query($sql) as $DATA)
    echo $DATA["name"] . "\t" . ($DATA["admin"]? "true": "false") . "\n";

  exit(0);
}

if($argv[1] == 'add' && $argc > 3 && $argc < 6)
{
  $user = $argv[2];
  $admin = !strcasecmp($argv[3], "true");
  $pass = ($argc > 4? md5($argv[4]): false);

  // prepare the SQL
  $sql = "INSERT INTO user (name, pass_md5, role_id) VALUES (";
  $sql .= $db->quote($user);
  $sql .= ", " . (empty($pass)? 'NULL': $db->quote($pass));
  $sql .= ", (SELECT id FROM role WHERE name = '"
    . ($admin? 'admin': 'user') . "')";
  $sql .= ")";

  if($db->exec($sql) != 1)
    die("cannot add user '$user'\n");

  exit(0);
}

if($argv[1] == 'rst' && $argc > 3 && $argc < 6)
{
  $user = $argv[2];
  $admin = !strcasecmp($argv[3], "true");
  $pass = ($argc > 4? md5($argv[4]): false);

  // prepare the SQL
  $sql = "UPDATE user SET pass_md5 = ";
  $sql .= (empty($pass)? 'NULL': $db->quote($pass));
  $sql .= ", role_id = (SELECT id FROM role WHERE name = '"
    . ($admin? 'admin': 'user') . "')";
  $sql .= " WHERE name = " . $db->quote($user);

  if($db->exec($sql) != 1)
    die("cannot reset user '$user'\n");

  exit(0);
}

if($argv[1] == 'passwd' && $argc > 2 && $argc < 5)
{
  $user = $argv[2];
  $pass = ($argc > 3? md5($argv[3]): false);

  // prepare the SQL
  $sql = "UPDATE user SET pass_md5 = ";
  $sql .= (empty($pass)? 'NULL': $db->quote($pass));
  $sql .= " WHERE name = " . $db->quote($user);

  if($db->exec($sql) != 1)
    die("cannot reset password for '$user'\n");

  exit(0);
}

if($argv[1] == 'rm' && $argc > 2)
{
  $user = $argv[2];
  $sql = "DELETE FROM user WHERE name = " . $db->quote($user);
  if($db->exec($sql) != 1)
    die("cannot remove user '$user'\n");

  exit(0);
}

echo "$argv[0]: bad arguments, see $argv[0] help\n";
exit(2);
