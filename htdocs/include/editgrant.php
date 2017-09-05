<?php
// process a grant update
require_once("grantfuncs.php");
require_once("pages.php");

function handleUpdate($DATA, $params)
{
  global $db;

  // handle parameters
  $values = array();
  $values['notify_email'] = $db->quote(fixEMailAddrs($params["notify"]));

  if(isset($params['comment']))
  {
    $comment = trim($params['comment']);
    $values['cmt'] = (empty($comment)? 'NULL': $db->quote($comment));
  }

  if(isset($params['pass_clear']) && $params['pass_clear'])
  {
    $values['pass_md5'] = 'NULL';
    $values['pass_ph'] = 'NULL';
  }
  elseif(!empty($params['pass']))
  {
    $values['pass_md5'] = 'NULL';
    $values['pass_ph'] = $db->quote(hashPassword($params['pass']));
  }

  if(isset($params['pass_send']) && $params['pass_send'])
    $values['pass_send'] = 1;
  else
    $values['pass_send'] = 0;

  if(isset($params['grant_permanent']) && $params['grant_permanent'])
  {
    $values['grant_last_time'] = 'NULL';
    $values['grant_expire'] = 'NULL';
    $values['grant_expire_uln'] = 'NULL';
  }
  else
  {
    if(empty($params['grant_totaldays']))
      $values['grant_expire'] = 'NULL';
    elseif(isset($params['grant_totaldays']))
      $values['grant_expire'] = (time() - $DATA["time"]) + $params["grant_totaldays"] * 3600 * 24;
    if(isset($params['grant_lastuldays']))
      $values['grant_last_time'] = (empty($params['grant_lastuldays'])? 'NULL': $params["grant_lastuldays"] * 3600 * 24);
    if(isset($params['grant_maxul']))
      $values['grant_expire_uln'] = (empty($params['grant_maxul'])? 'NULL': $DATA["uploads"] + (int)$params['grant_maxul']);
  }

  if(isset($params['ticket_permanent']) && $params['ticket_permanent'])
  {
    $values['last_time'] = 'NULL';
    $values['expire'] = 'NULL';
    $values['expire_dln'] = 'NULL';
  }
  else
  {
    if(empty($params['ticket_totaldays']))
      $values['expire'] = 'NULL';
    elseif(isset($params['ticket_totaldays']))
      $values['expire'] = $params["ticket_totaldays"] * 3600 * 24;
    if(isset($params['ticket_lastdldays']))
      $values['last_time'] = (empty($params['ticket_lastdldays'])? 'NULL': $params["ticket_lastdldays"] * 3600 * 24);
    if(isset($params['ticket_maxdl']))
      $values['expire_dln'] = (empty($params['ticket_maxdl'])? 'NULL': (int)$params['ticket_maxdl']);
  }

  // prepare the query
  $tmp = array();
  foreach($values as $k => $v) $tmp[] = "$k = $v";
  $sql = "UPDATE \"grant\" SET " . join(", ", $tmp)
    . " WHERE id = " . $db->quote($DATA["id"]);
  if($db->exec($sql) != 1)
    return false;

  // fetch defaults
  $sql = "SELECT * FROM \"grant\" WHERE id = " . $db->quote($DATA["id"]);
  $DATA = $db->query($sql)->fetch();
  $DATA['pass'] = (empty($params["pass"])? NULL: $_POST["pass"]);

  // trigger update hooks
  onGrantUpdate($DATA);

  return $DATA;
}


// fetch the grant id and check for permissions
$DATA = false;
$id = &$_REQUEST['id'];
if(empty($id) || !isGrantId($id))
  $id = false;
else
{
  $sql = "SELECT * FROM \"grant\" WHERE id = " . $db->quote($id);
  $DATA = $db->query($sql)->fetch();
  if($DATA === false || isGrantExpired($DATA)
  || (!$auth["admin"] && $DATA["user_id"] != $auth["id"]))
    $DATA = false;
}

// handle update
if($DATA)
{
  if(validateParams($grantEditParams, $_POST))
  {
    // if update succeeds, return to listings
    if(handleUpdate($DATA, $_POST))
      $DATA = false;
  }
}

// resulting page
$src = (array_key_exists(@$_REQUEST['src'], $pages)? $_REQUEST['src']: 'glist');
if($DATA === false)
  header("Location: " . tokenUrl($adminPath, array('a' => $src)));
else
  include("editgrants.php");
