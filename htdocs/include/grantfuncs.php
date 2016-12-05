<?php
// new grant shared functions
require_once("funcs.php");


function isGrantExpired($DATA, $now = NULL)
{
  if(!isset($now)) $now = time();
  return ($DATA["grant_expire"] && $DATA["grant_expire"] < $now);
}


function grantExpiration($DATA, &$expVal = NULL)
{
  if($DATA["grant_expire"])
  {
    $expVal = $DATA["grant_expire"] - time();
    return sprintf(T_("In %s"), humanTime($expVal));
  }

  $expVal = 4294967295;
  return ("<strong>" . T_("Never") . "</strong>");
}


function genGrant($params)
{
  global $auth, $locale, $db, $defaults, $passHasher;

  // generate new unique id
  $id = genGrantId();

  // defaults
  if(!isset($params["grant_total"]))
    $params["grant_total"] = $defaults['grant']['total'];

  // prepare data
  $sql = "INSERT INTO \"grant\" (id, user_id, grant_expire, cmt, pass_ph"
    . ", time, expire, last_time, expire_dln, notify_email, sent_email, locale) VALUES (";
  $sql .= $db->quote($id);
  $sql .= ", " . $auth['id'];
  $sql .= ", " . (($params["grant_total"] == 0)? 'NULL': time() + $params["grant_total"]);
  $sql .= ", " . (empty($params["comment"])? 'NULL': $db->quote($params["comment"]));
  $sql .= ", " . (empty($params["pass"])? 'NULL':
      $db->quote($passHasher->HashPassword($params["pass"])));
  $sql .= ", " . time();
  if(!empty($params["ticket_permanent"]))
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
    'required' 	     => true,
    'funcs'          => array('is_string', 'not_empty'),
  ),
  'comment'          => 'is_string',
  'pass'             => 'is_string',
  'grant_total'      => 'is_numeric_int',
  'ticket_total'     => 'is_numeric_int',
  'ticket_lastdl'    => 'is_numeric_int',
  'ticket_maxdl'     => 'is_numeric_int',
  'ticket_permanent' => 'is_bool',
  'send_to'          => 'is_string',
);

$grantNewParams = array
(
  'notify'            => array
  (
    'required' 	      => true,
    'funcs'           => array('is_string', 'not_empty'),
  ),
  'comment'           => 'is_string',
  'pass'              => 'is_string',
  'grant_totaldays'   => 'is_numeric',
  'ticket_totaldays'  => 'is_numeric',
  'ticket_lastdldays' => 'is_numeric',
  'ticket_maxdl'      => 'is_numeric_int',
  'ticket_permanent'  => 'is_numeric_int',
  'send_to'           => 'is_string',
);

$grantUseParams = array
(
  'comment'           => 'is_string',
);

?>
