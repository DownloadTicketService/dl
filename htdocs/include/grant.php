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
  includeTemplate("style/include/nogrant.php", array('id' => $id));
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
function failUpload($file)
{
  unlink($file);
  return false;
}

function handleUpload($GRANT, $FILE)
{
  global $dataDir, $db;

  // fix file size overflow (when possible) in php 5.4-5.5
  if($FILE['size'] < 0)
  {
    $FILE['size'] = filesize($FILE["tmp_name"]);
    if($FILE['size'] < 0)
    {
      logError($FILE["tmp_name"] . ": uncorrectable PHP file size overflow");
      return false;
    }
  }

  // generate new unique id/file name
  list($id, $tmpFile) = genTicketId($FILE["name"]);
  if(!move_uploaded_file($FILE["tmp_name"], $tmpFile))
  {
    logError("cannot move file " . $FILE["tmp_name"] . " into $tmpFile");
    return failUpload($tmpFile);
  }

  // convert the upload to a ticket
  $db->beginTransaction();

  $sql = "INSERT INTO ticket (id, user_id, name, path, size, cmt, pass_ph"
    . ", time, last_time, expire, expire_dln, locale) VALUES (";
  $sql .= $db->quote($id);
  $sql .= ", " . $GRANT['user_id'];
  $sql .= ", " . $db->quote(basename($FILE["name"]));
  $sql .= ", " . $db->quote($tmpFile);
  $sql .= ", " . $FILE["size"];
  $sql .= ", " . (empty($GRANT["cmt"])? 'NULL': $db->quote($GRANT["cmt"]));
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
    return failUpload($tmpFile);
  }

  // fetch defaults
  $sql = "SELECT * FROM ticket WHERE id = " . $db->quote($id);
  $DATA = $db->query($sql)->fetch();
  if(!empty($GRANT['pass'])) $DATA['pass'] = $GRANT['pass'];

  // trigger use hooks
  onGrantUse($GRANT, $DATA);

  return $DATA;
}


// handle the request
$DATA = false;
if(isset($_FILES["file"])
&& is_uploaded_file($_FILES["file"]["tmp_name"])
&& $_FILES["file"]["error"] == UPLOAD_ERR_OK)
{
  if(!empty($_SESSION['g'][$id]['pass']))
    $GRANT['pass'] = $_SESSION['g'][$id]['pass'];
  $DATA = handleUpload($GRANT, $_FILES["file"]);
}

// resulting page
if($DATA === false)
  include("grants.php");
else
{
  unset($ref);
  includeTemplate("style/include/grantr.php");

  // kill the session ASAP
  if($auth === false)
    session_destroy();
}

?>
