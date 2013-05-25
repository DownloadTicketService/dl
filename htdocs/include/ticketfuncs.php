<?php
// new ticket shared functions
require_once("funcs.php");


function isTicketExpired($DATA, $now = NULL)
{
  if(!isset($now)) $now = time();
  return (($DATA["expire"] && $DATA["expire"] < $now)
       || ($DATA["last_stamp"] && $DATA["last_time"] && ($DATA["last_stamp"] + $DATA["last_time"]) < $now)
       || ($DATA["expire_dln"] && $DATA["expire_dln"] <= $DATA["downloads"]));
}


function ticketExpiry($DATA)
{
  if($DATA["expire_dln"] || $DATA["last_time"])
  {
    if($DATA["last_stamp"] && $DATA["last_time"])
      return sprintf(T_("About %s"), humanTime($DATA["last_stamp"] + $DATA["last_time"] - time()));
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
  global $auth, $locale, $dataDir, $db, $defaults;

  // generate new unique id/file name
  list($id, $tmpFile) = genTicketId($FILE["name"]);
  if(!move_uploaded_file($FILE["tmp_name"], $tmpFile))
    return handleUploadFailure($tmpFile);

  // prepare data
  $sql = "INSERT INTO ticket (id, user_id, name, path, size, cmt, pass_md5"
    . ", time, expire, last_time, expire_dln, notify_email, sent_email, locale) VALUES (";
  $sql .= $db->quote($id);
  $sql .= ", " . $auth['id'];
  $sql .= ", " . $db->quote(basename($FILE["name"]));
  $sql .= ", " . $db->quote($tmpFile);
  $sql .= ", " . $FILE["size"];
  $sql .= ", " . (empty($params["comment"])? 'NULL': $db->quote($params["comment"]));
  $sql .= ", " . (empty($params["pass"])? 'NULL': $db->quote(md5($params["pass"])));
  $sql .= ", " . time();
  if(@$params["permanent"])
  {
    $sql .= ", NULL";
    $sql .= ", NULL";
    $sql .= ", NULL";
  }
  else
  {
    if(!isset($params["ticket_total"]) && !isset($params["ticket_lastdl"]) && !isset($params["ticket_maxdl"]))
    {
      $params["ticket_total"] = $defaults['ticket']['total'];
      $params["ticket_lastdl"] = $defaults['ticket']['lastdl'];
      $params["ticket_maxdl"] = $defaults['ticket']['maxdl'];
    }
    $sql .= ", " . (empty($params["ticket_total"])? 'NULL': time() + $params["ticket_total"]);
    $sql .= ", " . (empty($params["ticket_lastdl"])? 'NULL': $params["ticket_lastdl"]);
    $sql .= ", " . (empty($params["ticket_maxdl"])? 'NULL': (int)$params["ticket_maxdl"]);
  }
  $sql .= ", " . (empty($params["notify"])? 'NULL': $db->quote(fixEMailAddrs($params["notify"])));
  $sql .= ", " . (empty($params["send_to"])? 'NULL': $db->quote(fixEMailAddrs($params["send_to"])));
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
$ticketRestParams = array
(
  'comment'       => 'is_string',
  'pass'          => 'is_string',
  'ticket_total'  => 'is_numeric_int',
  'ticket_lastdl' => 'is_numeric_int',
  'ticket_maxdl'  => 'is_numeric_int',
  'notify'        => 'is_string',
  'send_to'       => 'is_string',
  'permanent'     => 'is_bool',
);

$ticketNewParams = array
(
  'comment'           => 'is_string',
  'pass'              => 'is_string',
  'ticket_totaldays'  => 'is_numeric',
  'ticket_lastdldays' => 'is_numeric',
  'ticket_maxdl'      => 'is_numeric_int',
  'ticket_permanent'  => 'is_numeric_int',
  'notify'            => 'is_string',
  'send_to'           => 'is_string',
);

$ticketEditParams = $ticketNewParams;
$ticketEditParams['clear'] = 'is_numeric_int';
$ticketEditParams['name'] = array
(
  'required' => true,
  'funcs'    => array('is_string', 'not_empty'),
);

?>
