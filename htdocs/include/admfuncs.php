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
  global $dataDir;

  // generate new unique id/file name
  if(!file_exists($dataDir)) mkdir($dataDir);
  do
  {
    list($usec, $sec) = microtime();
    $id = md5(rand() . "/$usec/$sec/" . $seed);
    $tmpFile = "$dataDir/$id";
  }
  while(fopen($tmpFile, "x") === FALSE);

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

  return ($db->exec($sql) == 1);
}


function userDel($user)
{
  global $db;
  $sql = "DELETE FROM user WHERE name = " . $db->quote($user);
  return ($db->exec($sql) == 1);
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

  $sql = "UPDATE user SET " . implode(", ", $fields)
    . " WHERE name = " . $db->quote($user);

  return ($db->exec($sql) == 1);
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

?>
