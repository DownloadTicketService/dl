<?php
set_magic_quotes_runtime(0);
if(!isset($argc)) die("not running from the command line\n");

// data
require_once("config.php");
$uDbPath = $spoolDir . "/user.db";

// initialize the dbs
$dbMode = (version_compare(PHP_VERSION, "4.3.5", "<")? "w": "c");
$uDb = dba_popen($uDbPath, $dbMode, $dbHandler) or die();

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
  rm user            : remove "user"

EOD
);
  exit(2);
}

if($argv[1] == 'list')
{
  echo "#user\tadm\n";

  for($key = dba_firstkey($uDb); $key; $key = dba_nextkey($uDb))
  {
    $DATA = dba_fetch($key, $uDb);
    if($DATA === false) continue;
    $DATA = unserialize($DATA);
    echo "$key\t" . ($DATA['admin']? "true": "false") . "\n";
  }

  exit(0);
}

if($argv[1] == 'add' && $argc > 3)
{
  $user = $argv[2];
  $admin = !strcasecmp($argv[3], "true");
  $pass = $argv[4];

  $DATA = array('admin' => $admin, 'pass' => $pass);
  @dba_insert($user, serialize($DATA), $uDb) or
    die("cannot add user '$user'\n");

  exit(0);
}

if($argv[1] == 'rm' && $argc > 2)
{
  $user = $argv[2];
  dba_delete($user, $uDb) or
    die("cannot remove user '$user'\n");
  exit(0);
}

echo "$argv[0]: bad arguments, see $argv[0] help\n";
exit(2);
