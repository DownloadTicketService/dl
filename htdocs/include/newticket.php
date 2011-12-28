<?php
// process a file submission

// upload handler
function failUpload($file)
{
  unlink($file);
  return false;
}

function handleUpload($FILE)
{
  global $auth, $locale, $dataDir, $db;

  // generate new unique id/file name
  list($id, $tmpFile) = genTicketId($FILE["name"]);
  if(!move_uploaded_file($FILE["tmp_name"], $tmpFile))
    return failUpload($tmpFile);

  // prepare data
  $sql = "INSERT INTO ticket (id, user_id, name, path, size, cmt, pass_md5"
    . ", time, last_time, expire, expire_dln, notify_email, sent_email, locale) VALUES (";
  $sql .= $db->quote($id);
  $sql .= ", " . $auth['id'];
  $sql .= ", " . $db->quote(basename($FILE["name"]));
  $sql .= ", " . $db->quote($tmpFile);
  $sql .= ", " . $FILE["size"];
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
  $sql .= ", " . (empty($_POST["st"])? 'NULL': $db->quote(fixEMailAddrs($_POST["st"])));
  $sql .= ", " . $db->quote($locale);
  $sql .= ")";

  if($db->exec($sql) != 1)
    return failUpload($tmpFile);

  // fetch defaults
  $sql = "SELECT * FROM ticket WHERE id = " . $db->quote($id);
  $DATA = $db->query($sql)->fetch();
  $DATA['pass'] = (empty($_POST["pass"])? NULL: $_POST["pass"]);

  // trigger creation hooks
  onTicketCreate($DATA);

  return $DATA;
}


// handle the request
$DATA = false;
if(isset($_FILES["file"])
&& is_uploaded_file($_FILES["file"]["tmp_name"])
&& $_FILES["file"]["error"] == UPLOAD_ERR_OK)
  $DATA = handleUpload($_FILES["file"]);

// resulting page
if($DATA !== false)
  include("newticketr.php");
else
  include("newtickets.php");
?>
