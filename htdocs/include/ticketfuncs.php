<?php
// new ticket shared functions
require_once("funcs.php");


function isTicketExpired($DATA, $now = NULL)
{
  if(!isset($now)) $now = time();
  return (($DATA["expire"] && $DATA["expire"] < $now)
       || ($DATA["expire_last"] && $DATA["expire_last"] < $now)
       || ($DATA["expire_dln"] && $DATA["expire_dln"] <= $DATA["downloads"]));
}


function ticketExpiry($DATA)
{
  if($DATA["expire_dln"] || $DATA["last_time"])
  {
    if($DATA["expire_last"])
      return sprintf(T_("About %s"), humanTime($DATA["expire_last"] - time()));
    elseif($DATA["expire_dln"] && $DATA["downloads"])
      return sprintf(T_("About %d downloads"), ($DATA["expire_dln"] - $DATA["downloads"]));
    elseif($DATA["expire"])
      return sprintf(T_("About %s"), humanTime($DATA["expire"] - time()));
    elseif($DATA["expire_dln"])
      return sprintf(T_("After %d downloads"), $DATA["expire_dln"]);
    else
      return sprintf(T_("%s after next download"), humanTime($DATA["last_time"]));
  }
  elseif($DATA["expire"])
    return sprintf(T_("In %s"), humanTime($DATA["expire"] - time()));

  return ("<strong>" . T_("Never") . "</strong>");
}


function handleUploadFailure($file)
{
  unlink($file);
  return false;
}

function handleUpload($FILE, $params)
{
  global $auth, $locale, $dataDir, $db;

  // generate new unique id/file name
  list($id, $tmpFile) = genTicketId($FILE["name"]);
  if(!move_uploaded_file($FILE["tmp_name"], $tmpFile))
    return handleUploadFailure($tmpFile);

  // prepare data
  $sql = "INSERT INTO ticket (id, user_id, name, path, size, cmt, pass_md5"
    . ", time, last_time, expire, expire_dln, notify_email, sent_email, locale) VALUES (";
  $sql .= $db->quote($id);
  $sql .= ", " . $auth['id'];
  $sql .= ", " . $db->quote(basename($FILE["name"]));
  $sql .= ", " . $db->quote($tmpFile);
  $sql .= ", " . $FILE["size"];
  $sql .= ", " . (empty($params["cmt"])? 'NULL': $db->quote($params["cmt"]));
  $sql .= ", " . (empty($params["pass"])? 'NULL': $db->quote(md5($params["pass"])));
  $sql .= ", " . time();
  if(!empty($params["nl"]))
  {
    $sql .= ", NULL";
    $sql .= ", NULL";
    $sql .= ", NULL";
  }
  else
  {
    $sql .= ", " . (empty($params["hra"])? 'NULL': $params["hra"] * 3600);
    $sql .= ", " . (empty($params["dn"])? 'NULL': time() + $params["dn"] * 3600 * 24);
    $sql .= ", " . (empty($params["dln"])? 'NULL': (int)$params["dln"]);
  }
  $sql .= ", " . (empty($params["nt"])? 'NULL': $db->quote(fixEMailAddrs($params["nt"])));
  $sql .= ", " . (empty($params["st"])? 'NULL': $db->quote(fixEMailAddrs($params["st"])));
  $sql .= ", " . $db->quote($locale);
  $sql .= ")";

  if($db->exec($sql) != 1)
    return handleUploadFailure($tmpFile);

  // fetch defaults
  $sql = "SELECT * FROM ticket WHERE id = " . $db->quote($id);
  $DATA = $db->query($sql)->fetch();
  $DATA['pass'] = (empty($params["pass"])? NULL: $params["pass"]);

  // trigger creation hooks
  onTicketCreate($DATA);

  return $DATA;
}


// parameters validation
$ticketNewParams = array
(
  'cmt'  => 'is_string',
  'pass' => 'is_string',
  'clr'  => 'is_numeric_int',
  'dn'   => 'is_numeric',
  'hra'  => 'is_numeric',
  'dln'  => 'is_numeric_int',
  'nl'   => 'is_numeric_int',
  'nt'   => 'is_string',
);

$ticketEditParams = $ticketNewParams;
$ticketEditParams['name'] = array('is_string', 'not_empty');

?>
