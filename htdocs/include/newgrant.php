<?php
// process a grant request

function handleGrant()
{
  global $auth, $db;

  // generate new unique id
  list($usec, $sec) = microtime();
  $id = md5(rand() . "/$usec/$sec/" . $_POST["nt"]);

  // prepare data
  $sql = "INSERT INTO grant (id, user_id, grant_expire, cmt, pass_md5"
    . ", time, last_time, expire, expire_dln, notify_email) VALUES (";
  $sql .= $db->quote($id);
  $sql .= ", " . $auth['id'];
  $sql .= ", " . (empty($_POST["gn"])? 'NULL': time() + $_POST["gn"] * 3600 * 24);
  $sql .= ", " . (empty($_POST["cmt"])? 'NULL': $db->quote($_POST["cmt"]));
  $sql .= ", " . (empty($_POST["pass"])? 'NULL': $db->quote(md5($_POST["pass"])));
  $sql .= ", " . time();
  if(!empty($_POST["nl"]))
  {
    $sql .= ", NULL";
    $sql .= ", NULL";
    $sql .= ", NULL";
  }
  else
  {
    $sql .= ", " . (empty($_POST["hra"])? 'NULL': $_POST["hra"] * 3600);
    $sql .= ", " . (empty($_POST["dn"])? 'NULL': time() + $_POST["dn"] * 3600 * 24);
    $sql .= ", " . (empty($_POST["dln"])? 'NULL': (int)$_POST["dln"]);
  }
  $sql .= ", " . (empty($_POST["nt"])? 'NULL': $db->quote(fixEMailAddrs($_POST["nt"])));
  $sql .= ")";

  if($db->exec($sql) != 1)
    return false;

  // fetch defaults
  $sql = "SELECT * FROM grant WHERE id = " . $db->quote($id);
  $DATA = $db->query($sql)->fetch();
  $DATA['pass'] = (empty($_POST["pass"])? NULL: $_POST["pass"]);
  $DATA['st'] = (empty($_POST["st"])? NULL: fixEMailAddrs($_POST["st"]));

  // trigger creation hooks
  onGrantCreate($DATA);

  return $DATA;
}


// resulting page
$DATA = false;
if(!empty($_POST["nt"]) && isset($_POST["gn"]))
  $DATA = handleGrant();

if($DATA !== false)
  include("include/newgrantr.php");
else
  include("include/newgrants.php");
?>
