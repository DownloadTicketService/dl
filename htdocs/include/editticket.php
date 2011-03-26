<?php
// process a ticket update

function handleUpdate($id)
{
  global $db;

  // handle parameters
  $values = array();

  if(!empty($_POST['name']))
    $values['name'] = $db->quote($_POST['name']);

  if(isset($_POST['cmt']))
    $values['cmt'] = (empty($_POST['cmt'])? 'NULL': $db->quote($_POST['cmt']));

  if(isset($_POST['clr']) && $_POST['clr'])
    $values['pass_md5'] = 'NULL';
  elseif(!empty($_POST['pass']))
    $values['pass_md5'] = $db->quote(md5($_POST['pass']));

  if(isset($_POST['nl']) && $_POST['nl'])
  {
    $values['last_time'] = 'NULL';
    $values['expire'] = 'NULL';
    $values['expire_dln'] = 'NULL';
  }
  else
  {
    if(isset($_POST['hra']))
      $values['last_time'] = (empty($_POST['hra'])? 'NULL': $_POST["hra"] * 3600);
    if(isset($_POST['dn']))
      $values['expire'] = (empty($_POST['dn'])? 'NULL': time() + $_POST["dn"] * 3600 * 24);
    if(isset($_POST['dln']))
      $values['expire_dln'] = (empty($_POST['dln'])? 'NULL': (int)$_POST['dln']);
  }

  if(isset($_POST['nt']))
    $values['notify_email'] = (empty($_POST['nt'])? 'NULL': $db->quote(fixEMailAddrs($_POST["nt"])));

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
if(empty($id) && !isTicketId($id))
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
  $params = array
  (
    'name' => array('is_string', 'not_empty'),
    'cmt'  => 'is_string',
    'pass' => 'is_string',
    'clr'  => 'is_numeric_int',
    'dn'   => 'is_numeric',
    'hra'  => 'is_numeric',
    'dln'  => 'is_numeric_int',
    'nl'   => 'is_numeric_int',
    'nt'   => 'is_string',
  );

  if(validateParams($params, $_POST))
  {
    // if update succeeds, return to listings
    if(handleUpdate($id))
      $DATA = false;
  }
}

// resulting page
if($DATA === false)
  header("Location: $adminPath?a=tlist");
else
  include("edittickets.php");
?>
