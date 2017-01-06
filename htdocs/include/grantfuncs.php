<?php
// new grant shared functions
require_once("funcs.php");
require_once("ticketfuncs.php");


function isGrantExpired($DATA, $now = NULL)
{
  if(!isset($now)) $now = time();
  return ($DATA["grant_expire"] && ($DATA["grant_expire"] + $DATA["time"]) < $now);
}


function grantExpiration($DATA, &$expVal = NULL)
{
  if($DATA["grant_expire"])
  {
    $expVal = $DATA["grant_expire"] + $DATA["time"] - time();
    return sprintf(T_("In %s"), humanTime($expVal));
  }

  $expVal = 4294967295;
  return ("<strong>" . T_("Never") . "</strong>");
}


function grantExpirationParams($params)
{
  global $defaults;

  // TODO: mostly a stub until grants work as tickets
  if(!isset($params["grant_total"]))
    $params["grant_total"] = $defaults['grant']['total'];
  $total = ($params["grant_total"] == 0)? 'NULL': $params["grant_total"];

  return array($total, false, false);
}


function genGrant($params)
{
  global $auth, $locale, $db, $passHasher;

  // generate new unique id
  $id = genGrantId();

  // parameters
  if(!empty($params["comment"]))
    $params["comment"] = trim($params["comment"]);

  // expiration values
  list($grant_total, $grant_lastdl, $grant_maxdl) = grantExpirationParams($params);
  list($ticket_total, $ticket_lastdl, $ticket_maxdl) = ticketExpirationParams($params);

  // prepare data
  $sql = "INSERT INTO \"grant\" (id, user_id, grant_expire, cmt, pass_ph"
    . ", time, expire, last_time, expire_dln, notify_email, sent_email, locale) VALUES (";
  $sql .= $db->quote($id);
  $sql .= ", " . $auth['id'];
  $sql .= ", " . $grant_total;
  $sql .= ", " . (empty($params["comment"])? 'NULL': $db->quote($params["comment"]));
  $sql .= ", " . (empty($params["pass"])? 'NULL':
      $db->quote($passHasher->HashPassword($params["pass"])));
  $sql .= ", " . time();
  $sql .= ", " . $ticket_total;
  $sql .= ", " . $ticket_lastdl;
  $sql .= ", " . $ticket_maxdl;
  $sql .= ", " . (empty($params["notify"])? 'NULL': $db->quote(fixEMailAddrs($params["notify"])));
  $sql .= ", " . (empty($params["send_to"])? 'NULL': $db->quote(fixEMailAddrs($params["send_to"])));
  $sql .= ", " . $db->quote($locale);
  $sql .= ")";

  if($db->exec($sql) != 1)
    return false;

  // fetch defaults
  $sql = "SELECT * FROM \"grant\" WHERE id = " . $db->quote($id);
  $DATA = $db->query($sql)->fetch();
  $DATA['pass'] = (empty($params["pass"])? NULL: $params["pass"]);

  // trigger creation hooks
  onGrantCreate($DATA);

  return $DATA;
}


// parameters validation
$grantRestParams = array
(
  'notify'           => array
  (
    'required'	     => true,
    'funcs'          => array('is_string', 'not_empty'),
  ),
  'comment'          => 'is_string',
  'pass'             => 'is_string',
  'grant_total'      => 'is_numeric_int',
  'ticket_total'     => 'is_numeric_int',
  'ticket_lastdl'    => 'is_numeric_int',
  'ticket_maxdl'     => 'is_numeric_int',
  'ticket_expiry'    => 'is_expiry_choice',
  'ticket_permanent' => 'is_bool',
  'send_to'          => 'is_string',
);

$grantNewParams = array
(
  'notify'            => array
  (
    'required'	      => true,
    'funcs'           => array('is_string', 'not_empty'),
  ),
  'comment'           => 'is_string',
  'pass'              => 'is_string',
  'grant_totaldays'   => 'is_numeric',
  'ticket_totaldays'  => 'is_numeric',
  'ticket_lastdldays' => 'is_numeric',
  'ticket_maxdl'      => 'is_numeric_int',
  'ticket_expiry'     => 'is_expiry_choice',
  'send_to'           => 'is_string',
);

$grantUseParams = array
(
  'comment'           => 'is_string',
);

?>
