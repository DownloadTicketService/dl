<?php
// process a grant
require_once("grantfuncs.php");

// try to fetch the grant
$id = $_REQUEST["g"];
if(!isGrantId($id))
{
  $id = false;
  $GRANT = false;
}
else
{
  $sql = "SELECT * FROM \"grant\" WHERE id = " . $db->quote($id);
  $GRANT = $db->query($sql)->fetch();
}

$ref = "$masterPath?g=$id";
if($GRANT === false || isGrantExpired($GRANT))
{
  includeTemplate("$style/include/nogrant.php", array('id' => $id));
  exit();
}

if(hasPassHash($GRANT) && !isset($_SESSION['g'][$id]))
{
  if(!empty($_POST['p']) && checkPassHash('"grant"', $GRANT, $_POST['p']))
  {
    // authorize the grant for this session
    $_SESSION['g'][$id] = array('pass' => $_POST["p"]);
  }
  else
  {
    include("grantp.php");
    exit();
  }
}


// upload handler
function useGrant($upload, $GRANT, $DATA)
{
  global $db;

  // populate comment with file list when empty
  if(!empty($DATA["cmt"]))
    $DATA["cmt"] = trim($DATA["cmt"]);
  if(empty($DATA["cmt"]) && count($upload['files']) > 1)
    $DATA["cmt"] = T_("Archive contents:") . "\n  " . implode("\n  ", $upload['files']);

  // convert the upload to a ticket
  $db->beginTransaction();

  $sql = "INSERT INTO ticket (id, user_id, name, path, size, cmt, pass_ph, pass_send"
    . ", time, last_time, expire, expire_dln, locale, from_grant) VALUES (";
  $sql .= $db->quote($upload['id']);
  $sql .= ", " . $GRANT['user_id'];
  $sql .= ", " . $db->quote($upload["name"]);
  $sql .= ", " . $db->quote($upload["path"]);
  $sql .= ", " . $upload["size"];
  $sql .= ", " . (empty($DATA["cmt"])? 'NULL': $db->quote($DATA["cmt"]));
  $sql .= ", " . (empty($GRANT["pass_ph"])? 'NULL': $db->quote($GRANT["pass_ph"]));
  $sql .= ", " . (int)$GRANT["pass_send"];
  $sql .= ", " . time();
  $sql .= ", " . (empty($GRANT["last_time"])? 'NULL': $GRANT['last_time']);
  $sql .= ", " . (empty($GRANT["expire"])? 'NULL': $GRANT['expire']);
  $sql .= ", " . (empty($GRANT["expire_dln"])? 'NULL': $GRANT['expire_dln']);
  $sql .= ", " . (empty($GRANT["locale"])? 'NULL': $db->quote($GRANT['locale']));
  $sql .= ", " . $db->quote($GRANT['id']);
  $sql .= ")";
  $db->exec($sql);

  // check for validity after upload
  ++$GRANT["uploads"];
  if(isGrantExpired($GRANT))
  {
    $sql = "DELETE FROM \"grant\" WHERE id = " . $db->quote($GRANT['id']);
    $db->exec($sql);
  }
  else
  {
    $now = time();
    $sql = "UPDATE \"grant\" SET last_stamp = $now"
	 . ", uploads = uploads + 1 WHERE id = " . $db->quote($GRANT['id']);
    $db->exec($sql);
  }

  if(!$db->commit())
  {
    logDBError($db, "cannot commit new ticket to database");
    return false;
  }

  // fetch defaults
  $sql = "SELECT * FROM ticket WHERE id = " . $db->quote($upload['id']);
  $TICKET = $db->query($sql)->fetch();
  if(!empty($GRANT['pass'])) $TICKET['pass'] = $GRANT['pass'];

  // trigger use hooks
  onGrantUse($GRANT, $TICKET);

  return array($GRANT, $TICKET);
}


// handle the request
$TICKET = false;
$FILES = uploadedFiles($_FILES["file"]);
if($FILES !== false && validateParams($grantUseParams, $_POST))
{
  if(!empty($_SESSION['g'][$id]['pass']))
    $GRANT['pass'] = $_SESSION['g'][$id]['pass'];

  $DATA = array();
  if(!empty($_POST['comment']))
    $DATA['cmt'] = $_POST['comment'];

  $ret = withUpload($FILES, 'useGrant', array($GRANT, $DATA));
  if($ret !== false)
    list($GRANT, $TICKET) = $ret;
}

// resulting page
if($TICKET === false)
  include("grants.php");
else
{
  if(isGrantExpired($GRANT))
    unset($ref);
  includeTemplate("$style/include/grantr.php");

  // kill the session ASAP
  if($auth === false)
    session_destroy();
}
