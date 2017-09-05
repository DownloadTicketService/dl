<?php
// administrative functions
require_once("funcs.php");
require_once("fatal.php");


function restart_session()
{
  session_regenerate_id();
  $_SESSION['token'] = randomToken();
}


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

  if($db->exec("DELETE FROM \"grant\" WHERE id = ". $db->quote($DATA["id"])) == 1)
    onGrantPurge($DATA, $auto);
}


function init()
{
  global $gcInternal, $gcProbability;

  if($gcInternal === true
  && ($gcProbability === 1.
   || (mt_rand() / mt_getrandmax() < $gcProbability)))
    runGc();
}


function runGc()
{
  global $db, $gcLimit;

  $now = time();

  $sql = "SELECT * FROM ticket WHERE (expire + time) < $now";
  $sql .= " OR (last_stamp + last_time) < $now";
  $sql .= " OR expire_dln <= downloads";
  if($gcLimit) $sql .= " LIMIT $gcLimit";
  foreach($db->query($sql)->fetchAll() as $DATA)
    ticketPurge($DATA);

  // expire grants
  $sql = "SELECT * FROM \"grant\" WHERE (grant_expire + time) < $now";
  $sql .= " OR (last_stamp + grant_last_time) < $now";
  $sql .= " OR grant_expire_uln <= uploads";
  if($gcLimit) $sql .= " LIMIT $gcLimit";
  foreach($db->query($sql)->fetchAll() as $DATA)
    grantPurge($DATA);
}


function genTicketId()
{
  global $dataDir, $maxUUTries;

  $tries = $maxUUTries;
  do
  {
    $id = randomToken();
    $tmpFile = "$dataDir/$id";
  }
  while(@fopen($tmpFile, "x") === FALSE && --$tries);
  if(!$tries)
  {
    logError("cannot generate unique ticket ID");
    httpInternalError();
  }

  return array($id, $tmpFile);
}


function genGrantId()
{
  global $db, $maxUUTries;

  $q = $db->prepare('SELECT id FROM "grant" WHERE id = :id');
  $tries = $maxUUTries;
  do
  {
    $id = randomToken();
    $q->closeCursor();
    $q->execute(array(':id' => $id));
  }
  while($q->fetch() !== FALSE && --$tries);
  if(!$tries)
  {
    logError("cannot generate unique grant ID");
    httpInternalError();
  }

  return $id;
}


function userAdd($user, $pass, $admin, $email = false)
{
  global $db, $maxUserLen, $maxPassLen;

  // validate user/password sizes
  if(strlen($user) > $maxUserLen || strlen($pass) > $maxPassLen)
    return false;

  // prepare the SQL
  $sql = 'INSERT INTO "user" (name, pass_ph, role_id, email) VALUES (';
  $sql .= $db->quote($user);
  $sql .= ", " . (empty($pass)? 'NULL': $db->quote(hashPassword($pass)));
  $sql .= ", (SELECT id FROM role WHERE name = '"
    . ($admin? 'admin': 'user') . "')";
  $sql .= ", " . (empty($email)? 'NULL': $db->quote($email));
  $sql .= ")";

  $ret = ($db->exec($sql) == 1);
  logEvent("adding user $user: " . ($ret? "success": "fail"),
      ($ret? LOG_INFO: LOG_ERR));
  return $ret;
}


function userDel($user)
{
  global $db;
  $sql = 'DELETE FROM "user" WHERE name = ' . $db->quote($user);
  $ret = ($db->exec($sql) == 1);
  logEvent("deleting user $user: " . ($ret? "success": "fail"),
      ($ret? LOG_INFO: LOG_ERR));
  return $ret;
}


function userUpd($user, $pass = null, $admin = null, $email = null)
{
  global $db, $maxUserLen, $maxPassLen;

  // validate user/password sizes
  if(strlen($user) > $maxUserLen || strlen($pass) > $maxPassLen)
    return false;

  // prepare the SQL
  $fields = array();
  if(!is_null($pass))
  {
    $fields[] = "pass_md5 = NULL";
    $fields[] = "pass_ph = " . (empty($pass)? 'NULL': $db->quote(hashPassword($pass)));
  }
  if(!is_null($admin))
  {
    $fields[] = "role_id = (SELECT id FROM role WHERE name = '"
      . ($admin? 'admin': 'user') . "')";
  }
  if(!is_null($email))
  {
    $fields[] = "email = " . (empty($email)? 'NULL': $db->quote($email));
  }
  if(!count($fields))
    return false;

  $sql = 'UPDATE "user" SET ' . implode(", ", $fields)
    . " WHERE name = " . $db->quote($user);
  $ret = ($db->exec($sql) == 1);

  $msg = array();
  if(!is_null($pass)) $msg[] = "password";
  if(!is_null($admin)) $msg[] = "role";
  if(!is_null($email)) $msg[] = "email";
  logEvent("updating user $user (" . join(", ", $msg)
      . "): " . ($ret? "success": "fail"), ($ret? LOG_INFO: LOG_ERR));
  return $ret;
}


function userAdm($user)
{
  global $db;

  $sql = 'SELECT u.name, admin FROM "user" u'
    . " LEFT JOIN role r ON r.id = u.role_id"
    . " WHERE u.name = " . $db->quote($user);
  $DATA = $db->query($sql)->fetch();

  return ($DATA? $DATA['admin']: null);
}


function userCheck($user, $pass)
{
  return userLogin($user, $pass, false);
}


function hasPassHash($DATA)
{
  return (isset($DATA['pass_ph']) || isset($DATA['pass_md5']));
}


function checkPassHash($table, $DATA, $pass)
{
  global $db, $maxPassLen;

  // validate password size
  if(strlen($pass) > $maxPassLen)
    return false;

  if(!$DATA || empty($pass) || isset($DATA['pass_ph']))
  {
    $hash = ($DATA !== false? $DATA['pass_ph']: '*');
    $okpass = password_verify($pass, $hash);
  }
  else
  {
    // legacy upgrade
    $okpass = (md5($pass) === $DATA['pass_md5']);
    if($okpass)
    {
      $id = $DATA['id'];
      $DATA['pass_md5'] = NULL;
      $DATA['pass_ph'] = hashPassword($pass);
      $sql = "UPDATE $table"
	. " SET pass_ph = " . $db->quote($DATA['pass_ph'])
	. ", pass_md5 = NULL WHERE id = " . $db->quote($id);
      $ret = ($db->exec($sql) == 1);
      logEvent("upgrading password hash of $table/$id: "
	  . ($ret? "success": "fail"), ($ret? LOG_INFO: LOG_ERR));
    }
  }

  return $okpass;
}


function userLogin($user, $pass, $rmt, $email = false)
{
  global $db, $maxUserLen, $maxPassLen;

  // validate user/password sizes
  if(strlen($user) > $maxUserLen || strlen($pass) > $maxPassLen)
    return false;

  // fetch the user
  $sql = 'SELECT u.id, u.name, pass_md5, pass_ph, admin, email FROM "user" u'
    . " LEFT JOIN role r ON r.id = u.role_id"
    . " WHERE u.name = " . $db->quote($user);
  $DATA = $db->query($sql)->fetch();

  // remote auth doesn't check pass, but still needs an id stub
  if($rmt)
  {
    if(!$DATA)
    {
      // create a stub user and get the id
      $sql = 'INSERT INTO "user" (name, role_id, email) VALUES (';
      $sql .= $db->quote($user);
      $sql .= ", (SELECT id FROM role WHERE name = 'user')";
      $sql .= ", " . (empty($email)? 'NULL': $db->quote($email));
      $sql .= ")";
      if($db->exec($sql) != 1) return false;

      // fetch defaults
      $sql = 'SELECT u.id, u.name, admin, email FROM "user" u';
      $sql .= " LEFT JOIN role r ON r.id = u.role_id";
      $sql .= " WHERE u.name = " . $db->quote($user);
      $DATA = $db->query($sql)->fetch();
    }

    return $DATA;
  }

  // validate the user
  $ret = checkPassHash('user', $DATA, $pass);
  logEvent("login attempt for user $user: " . ($ret? "success": "fail"),
      ($ret? LOG_INFO: LOG_ERR));
  return ($ret? $DATA: false);
}
