<?php
// database handling functions
require_once("confwrap.php");


// a simple wrapper to handle some DB issues uniformly
class XPDO extends PDO
{
  public function __construct($dns, $dbUser, $dbPassword)
  {
    parent::__construct($dns, $dbUser, $dbPassword);

    // put the drivers directly into ANSI mode
    $driver = $this->getAttribute(PDO::ATTR_DRIVER_NAME);
    switch($driver)
    {
    case 'sqlite':
      $ret = $this->exec('PRAGMA foreign_keys = ON');
      break;

    case 'mysql':
      $ret = $this->exec('SET SQL_MODE = ANSI_QUOTES');
      break;

    default:
      $ret = 0;
      break;
    }

    // check status
    if($ret === False)
    {
      $err = $this->errorInfo();
      throw new PDOException('cannot switch driver into ANSI mode: ' . $err[2]);
    }
  }

  public function ping()
  {
    return ($this->exec('SELECT 1') == 1);
  }
}


// initialize the database connection
function connectDB($checkSchema = True)
{
  global $db, $dsn, $dbUser, $dbPassword, $schemaVersion;

  // initialize the db
  try { $db = new XPDO($dsn, $dbUser, $dbPassword); }
  catch(PDOException $e) { die("cannot initialize database\n"); }

  if($checkSchema)
  {
    // check schema version
    $sql = "SELECT value FROM config WHERE name = 'version'";
    if(!($q = $db->query($sql)))
      die("cannot initialize database\n");
    $version = $q->fetchColumn();
    if(version_compare($version, $schemaVersion, "!="))
      die("database requires schema upgrade\n");
  }
}


// check an existing DB connection for liveness and re-connect if needed
function reconnectDB()
{
  global $db;
  if(!$db->ping())
    connectDB(false);
}
