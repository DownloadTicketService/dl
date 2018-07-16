<?php
// database handling functions
require_once("confwrap.php");

require_once(__DIR__."/../../vendor/autoload.php");

final class DBConnection {
    protected static $instance = null;
    protected $conn;
    protected $queries;
    
    public static function getInstance() {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }
    
    protected function __construct() {
        GLOBAL $dsn2;
        $this->conn = \Doctrine\DBAL\DriverManager::getConnection([ 'url' => $dsn2 ], new \Doctrine\DBAL\Configuration());
    }
    
    /**
     * Me not like clones! Me smash clones!
     */
    protected function __clone() { }
    
    /**
     * 
     */
    public function getGenTicketQuery() {
        return $this->conn->createQueryBuilder()->insert("ticket")->
                                                        values([ 'id'        => '?',
                                                            'user_id'   => '?',
                                                            'name'      => '?',
                                                            'path'      => '?',
                                                            'size'      => '?',
                                                            'cmt'       => '?',
                                                            'pass_ph'   => '?',
                                                            'pass_send' => '?',
                                                            'time'      => '?',
                                                            'expire'    => '?',
                                                            'last_time' => '?',
                                                            'expire_dln'=> '?',
                                                            'notify_email'=>'?',
                                                            'sent_email' => '?',
                                                            'locale' => '?']);
    }
    
    public function getTicketById($id) {
        return $this->conn->createQueryBuilder()->select("*")->
                                                    from("ticket")->
                                                    where("id = ?")->
                                                    setParameter(0,$id)->execute()->fetch();
    }
    
    public function purgeTicketById($id) {
        return (1===$this->conn->createQueryBuilder()->delete("ticket")
                                                        ->where("id = ?")
                                                        ->setParameter(0,$id)->execute() );
    }
    
    public function purgeGrantById($id) {
        return (1===$this->conn->createQueryBuilder()->delete("grant")
                                                        ->where("id = ?")
                                                        ->setParameter(0,$id)->execute() );
    }
    
    public function getTicketsToPurge($now,$limit) {
        return $this->conn->createQueryBuilder()->select("*")->
                                                    from("ticket")->
                                                        where("(expire + time) < ?")->
                                                        or("(last_stamp + last_time) < ?")->
                                                        or("OR expire_dln <= downloads")->
                                                        setMaxResults($limit)->
                                                        setParameter(0,$now)->
                                                        setParameter(1,$now)->execute()->fetchAll();
    }
    public function getGrantsToPurge($now,$limit) {
        return $this->conn->createQueryBuilder()->select("*")->
                                                    from("grant")->
                                                        where("(grant_expire + time) < ?")->
                                                        or("(last_stamp + grant_last_time) < ?")->
                                                        or("OR grant_expire_uln <= uploads")->
                                                        setMaxResults($limit)->
                                                        setParameter(0,$now)->
                                                        setParameter(1,$now)->execute()->fetchAll();
    }
}


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
      $this->exec('PRAGMA foreign_keys = ON');
      break;

    case "mysql":
      // put MySQL into ANSI mode
      $this->exec('SET SQL_MODE = ANSI');
      break;
    }
  }

  public function ping()
  {
    try { return (@$this->exec('SELECT 1') == 1); }
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
