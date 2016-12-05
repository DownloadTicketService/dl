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
function useGrant($upload, $GRANT)
{
  global $db;

  // populate comment with file list when empty
  $cmt = $GRANT["cmt"];
  if(empty($cmt) && count($upload['files']) > 1)
    $cmt = T_("Archive contents:") . "\n  " . implode("\n  ", $upload['files']);

  // convert the upload to a ticket
  $db->beginTransaction();

  $sql = "INSERT INTO ticket (id, user_id, name, path, size, cmt, pass_ph"
    . ", time, last_time, expire, expire_dln, locale) VALUES (";
  $sql .= $db->quote($upload['id']);
  $sql .= ", " . $GRANT['user_id'];
  $sql .= ", " . $db->quote($upload["name"]);
  $sql .= ", " . $db->quote($upload["path"]);
  $sql .= ", " . $upload["size"];
  $sql .= ", " . (empty($cmt)? 'NULL': $db->quote($cmt));
  $sql .= ", " . (empty($GRANT["pass_ph"])? 'NULL': $db->quote($GRANT["pass_ph"]));
  $sql .= ", " . time();
  $sql .= ", " . (empty($GRANT["last_time"])? 'NULL': $GRANT['last_time']);
  $sql .= ", " . (empty($GRANT["expire"])? 'NULL': $GRANT['expire']);
  $sql .= ", " . (empty($GRANT["expire_dln"])? 'NULL': $GRANT['expire_dln']);
  $sql .= ", " . (empty($GRANT["locale"])? 'NULL': $db->quote($GRANT['locale']));
  $sql .= ")";
  $db->exec($sql);

  $sql = "DELETE FROM \"grant\" WHERE id = " . $db->quote($GRANT['id']);
  $db->exec($sql);

  if(!$db->commit())
  {
    logDBError($db, "cannot commit new ticket to database");
    return false;
  }

  // fetch defaults
  $sql = "SELECT * FROM ticket WHERE id = " . $db->quote($upload['id']);
  $DATA = $db->query($sql)->fetch();
  if(!empty($GRANT['pass'])) $DATA['pass'] = $GRANT['pass'];

  // trigger use hooks
  onGrantUse($GRANT, $DATA);

  return $DATA;
}


// handle the request
$DATA = false;
$FILES = uploadedFiles($_FILES["file"]);
if($FILES !== false)
{
  if(!empty($_SESSION['g'][$id]['pass']))
    $GRANT['pass'] = $_SESSION['g'][$id]['pass'];
  $DATA = withUpload($FILES, 'useGrant', $GRANT);
}

// resulting page
if($DATA === false)
  include("grants.php");
else
{
  unset($ref);
  includeTemplate("$style/include/grantr.php");

  // kill the session ASAP
  if($auth === false)
    session_destroy();
}

?>
