<?php
// process a ticket update
require_once("ticketfuncs.php");
require_once("pages.php");

function handleUpdate($id)
{
  global $db, $passHasher;

  // handle parameters
  $values = array();

  if(!empty($_POST['name']))
    $values['name'] = $db->quote(mb_sanitize($_POST['name']));

  if(isset($_POST['comment']))
  {
    $comment = trim($_POST['comment']);
    $values['cmt'] = (empty($comment)? 'NULL': $db->quote($comment));
  }

  if(isset($_POST['clear']) && $_POST['clear'])
  {
    $values['pass_md5'] = 'NULL';
    $values['pass_ph'] = 'NULL';
  }
  elseif(!empty($_POST['pass']))
  {
    $values['pass_md5'] = 'NULL';
    $values['pass_ph'] = $db->quote($passHasher->HashPassword($_POST['pass']));
  }

  if(isset($_POST['ticket_permanent']) && $_POST['ticket_permanent'])
  {
    $values['last_time'] = 'NULL';
    $values['expire'] = 'NULL';
    $values['expire_dln'] = 'NULL';
  }
  else
  {
    if(isset($_POST['ticket_totaldays']))
      $values['expire'] = (empty($_POST['ticket_totaldays'])? 'NULL': time() + $_POST["ticket_totaldays"] * 3600 * 24);
    if(isset($_POST['ticket_lastdldays']))
      $values['last_time'] = (empty($_POST['ticket_lastdldays'])? 'NULL': $_POST["ticket_lastdldays"] * 3600 * 24);
    if(isset($_POST['ticket_maxdl']))
      $values['expire_dln'] = (empty($_POST['ticket_maxdl'])? 'NULL': (int)$_POST['ticket_maxdl']);
  }

  if(isset($_POST['notify']))
    $values['notify_email'] = (empty($_POST['notify'])? 'NULL': $db->quote(fixEMailAddrs($_POST["notify"])));

  // prepare the query
  $tmp = array();
  foreach($values as $k => $v) $tmp[] = "$k = $v";
  $sql = "UPDATE ticket SET " . join(", ", $tmp)
    . " WHERE id = " . $db->quote($id);
  if($db->exec($sql) != 1)
    return false;

  // fetch defaults
  $sql = "SELECT * FROM ticket WHERE id = " . $db->quote($id);
  $DATA = $db->query($sql)->fetch();
  $DATA['pass'] = (empty($_POST["pass"])? NULL: $_POST["pass"]);

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
    if(handleUpdate($id))
      $DATA = false;
  }
}

// resulting page
$src = (array_key_exists(@$_REQUEST['src'], $pages)? $_REQUEST['src']: 'tlist');
if($DATA === false)
  header("Location: " . tokenUrl($adminPath, array('a' => $src)));
else
  include("edittickets.php");
?>
