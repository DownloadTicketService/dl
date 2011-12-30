<?php
// administrative functions
require_once("funcs.php");


function ticketPurge($DATA, $auto = true)
{
  global $db;

  if($db->exec("DELETE FROM ticket WHERE id = ". $db->quote($DATA["id"])) == 1)
  {
    unlink($DATA["path"]);
    onTicketPurge($DATA, $auto);
  }
}


function grantPurge($DATA, $auto = true)
{
  global $db;

  if($db->exec("DELETE FROM grant WHERE id = ". $db->quote($DATA["id"])) == 1)
    onGrantPurge($DATA, $auto);
}


function runGc()
{
  global $db, $gcLimit;

  $now = time();

  $sql = "SELECT * FROM ticket WHERE expire < $now";
  $sql .= " OR expire_last < $now";
  $sql .= " OR expire_dln <= downloads";
  if($gcLimit) $sql .= " LIMIT $gcLimit";
  foreach($db->query($sql)->fetchAll() as $DATA)
    ticketPurge($DATA);

  // expire grants
  $sql = "SELECT * FROM grant WHERE grant_expire < $now";
  if($gcLimit) $sql .= " LIMIT $gcLimit";
  foreach($db->query($sql)->fetchAll() as $DATA)
    grantPurge($DATA);
}


function genTicketId($seed)
{
  global $dataDir, $maxUUTries;

  // generate new unique id/file name
  if(!file_exists($dataDir)) mkdir($dataDir);

  $tries = $maxUUTries;
  do
  {
    list($usec, $sec) = microtime();
    $id = md5(rand() . "/$usec/$sec/" . $seed);
    $tmpFile = "$dataDir/$id";
  }
  while(fopen($tmpFile, "x") === FALSE && --$tries);
  if(!$tries)
  {
    logEvent("cannot generate unique ticket ID");
    header("HTTP/1.0 500 Internal Server Error");
    exit();
  }

  return array($id, $tmpFile);
}


function userAdd($user, $pass, $admin)
{
  global $db;

  // prepare the SQL
  $sql = "INSERT INTO user (name, pass_md5, role_id) VALUES (";
  $sql .= $db->quote($user);
  $sql .= ", " . (empty($pass)? 'NULL': $db->quote(md5($pass)));
  $sql .= ", (SELECT id FROM role WHERE name = '"
    . ($admin? 'admin': 'user') . "')";
  $sql .= ")";

  $ret = ($db->exec($sql) == 1);
  logEvent("adding user $user: " . ($ret? "success": "fail"));
  return $ret;
}


function userDel($user)
{
  global $db;
  $sql = "DELETE FROM user WHERE name = " . $db->quote($user);
  $ret = ($db->exec($sql) == 1);
  logEvent("deleting user $user: " . ($ret? "success": "fail"));
  return $ret;
}


function userUpd($user, $pass = null, $admin = null)
{
  global $db;

  // prepare the SQL
  $fields = array();
  if(!is_null($pass))
    $fields[] = "pass_md5 = " . (empty($pass)? 'NULL': $db->quote(md5($pass)));
  if(!is_null($admin))
  {
    $fields[] = "role_id = (SELECT id FROM role WHERE name = '"
      . ($admin? 'admin': 'user') . "')";
  }
  if(!count($fields))
    return false;

  $sql = "UPDATE user SET " . implode(", ", $fields)
    . " WHERE name = " . $db->quote($user);
  $ret = ($db->exec($sql) == 1);

  $msg = array();
  if(!is_null($pass)) $msg[] = "password";
  if(!is_null($admin)) $msg[] = "role";
  logEvent("updating user $user (" . join(", ", $msg)
    . "): " . ($ret? "success": "fail"));
  return $ret;
}


function userAdm($user)
{
  global $db;

  $sql = "SELECT u.name, admin FROM user u"
    . " LEFT JOIN role r ON r.id = u.role_id"
    . " WHERE u.name = " . $db->quote($user);
  $DATA = $db->query($sql)->fetch();

  return ($DATA? $DATA['admin']: null);
}


function userLogin($user, $pass, $rmt)
{
  global $db;

  // validate the user
  $sql = "SELECT u.id, u.name, pass_md5, admin FROM user u"
    . " LEFT JOIN role r ON r.id = u.role_id"
    . " WHERE u.name = " . $db->quote($user);
  $DATA = $db->query($sql)->fetch();
  if($DATA !== false)
    $okpass = (isset($rmt) || ($pass === $DATA['pass_md5']));
  else
  {
    $okpass = isset($rmt);
    if($okpass)
    {
      // create a stub user and get the id
      $sql = "INSERT INTO user (name, role_id) VALUES (";
      $sql .= $db->quote($user);
      $sql .= ", (SELECT id FROM role WHERE name = 'user')";
      $sql .= ")";
      if($db->exec($sql) != 1) return false;

      // fetch defaults
      $sql = "SELECT u.id, u.name, admin FROM user u";
      $sql .= " LEFT JOIN role r ON r.id = u.role_id";
      $sql .= " WHERE u.name = " . $db->quote($user);
      $DATA = $db->query($sql)->fetch();
    }
  }

  return ($okpass? $DATA: false);
}

?>
