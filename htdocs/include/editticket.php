<?php
// process a ticket update
require_once("ticketfuncs.php");
require_once("pages.php");

function handleUpdate($DATA, $params)
{
  global $db, $passHasher;

  // handle parameters
  $values = array();

  if(!empty($params['name']))
    $values['name'] = $db->quote(mb_sanitize($params['name']));

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
    $values['pass_ph'] = $db->quote($passHasher->HashPassword($params['pass']));
  }

  if(isset($params['pass_send']) && $params['pass_send'])
    $values['pass_send'] = 1;
  else
    $values['pass_send'] = 0;

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
      $values['expire'] = (time() - $DATA["time"]) + $params["ticket_totaldays"] * 3600 * 24;
    if(isset($params['ticket_lastdldays']))
      $values['last_time'] = (empty($params['ticket_lastdldays'])? 'NULL': $params["ticket_lastdldays"] * 3600 * 24);
    if(isset($params['ticket_maxdl']))
      $values['expire_dln'] = (empty($params['ticket_maxdl'])? 'NULL': $DATA["downloads"] + (int)$params['ticket_maxdl']);
  }

  if(isset($params['notify']))
    $values['notify_email'] = (empty($params['notify'])? 'NULL': $db->quote(fixEMailAddrs($params["notify"])));

  // prepare the query
  $tmp = array();
  foreach($values as $k => $v) $tmp[] = "$k = $v";
  $sql = "UPDATE ticket SET " . join(", ", $tmp)
    . " WHERE id = " . $db->quote($DATA["id"]);
  if($db->exec($sql) != 1)
    return false;

  // fetch defaults
  $sql = "SELECT * FROM ticket WHERE id = " . $db->quote($DATA["id"]);
  $DATA = $db->query($sql)->fetch();
  $DATA['pass'] = (empty($params["pass"])? NULL: $_POST["pass"]);

  // trigger update hooks
  onTicketUpdate($DATA);

  return $DATA;
}


// fetch the ticket id and check for permissions
$DATA = false;
$id = &$_REQUEST['id'];
if(empty($id) || !isTicketId($id))
  $id = false;
else
{
  $sql = "SELECT * FROM ticket WHERE id = " . $db->quote($id);
  $DATA = $db->query($sql)->fetch();
  if($DATA === false || isTicketExpired($DATA)
  || (!$auth["admin"] && $DATA["user_id"] != $auth["id"]))
    $DATA = false;
}

// handle update
if($DATA)
{
  if(validateParams($ticketEditParams, $_POST))
  {
    // if update succeeds, return to listings
    if(handleUpdate($DATA, $_POST))
      $DATA = false;
  }
}

// resulting page
$src = (array_key_exists(@$_REQUEST['src'], $pages)? $_REQUEST['src']: 'tlist');
if($DATA === false)
  header("Location: " . tokenUrl($adminPath, array('a' => $src)));
else
  include("edittickets.php");
