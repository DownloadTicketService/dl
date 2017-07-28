<?php
// database handling functions
require_once("confwrap.php");


// a simple wrapper to handle some DB issues uniformly
class XPDO extends PDO
{
  public function driver()
  {
    return $this->getAttribute(PDO::ATTR_DRIVER_NAME);
  }

  public function __construct($dns, $dbUser, $dbPassword)
  {
    parent::__construct($dns, $dbUser, $dbPassword);

    // make errors exceptional
    $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    switch($this->driver())
    {
    case "sqlite":
      // enforce foreign keys by default
      $ret = $this->exec('PRAGMA foreign_keys = ON');
      break;

    case "mysql":
      // put MySQL into ANSI mode
      $ret = $this->exec('SET SQL_MODE = ANSI_QUOTES');
      break;
    }
  }

  public function ping()
  {
    try { return ($this->exec('SELECT 1') == 1); }
    catch(PDOException $e) { return false; }
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
