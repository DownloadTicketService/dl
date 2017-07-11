<?php
// new grant shared functions
require_once("funcs.php");
require_once("ticketfuncs.php");


function isGrantExpired($DATA, $now = NULL)
{
  if(!isset($now)) $now = time();
  return (($DATA["grant_expire"] && ($DATA["grant_expire"] + $DATA["time"]) < $now)
       || ($DATA["last_stamp"] && $DATA["grant_last_time"] && ($DATA["last_stamp"] + $DATA["grant_last_time"]) < $now)
       || ($DATA["grant_expire_uln"] && $DATA["grant_expire_uln"] <= $DATA["uploads"]));
}


function grantExpiration($DATA, &$expVal = NULL)
{
  if($DATA["grant_expire_uln"] || $DATA["grant_last_time"])
  {
    if($DATA["last_stamp"] && $DATA["grant_last_time"])
    {
      $expVal = $DATA["last_stamp"] + $DATA["grant_last_time"] - time();
      return sprintf(T_("About %s"), humanTime($expVal));
    }
    elseif($DATA["grant_expire_uln"] && $DATA["uploads"])
    {
      $expVal = ($DATA["grant_expire_uln"] - $DATA["uploads"]);
      return sprintf(T_("About %d uploads"), $expVal);
    }
    elseif($DATA["grant_expire"])
    {
      $expVal = $DATA["grant_expire"] + $DATA["time"] - time();
      return sprintf(T_("About %s"), humanTime($expVal));
    }
    elseif($DATA["grant_expire_uln"])
    {
      $expVal = $DATA["grant_expire_uln"];
      return sprintf(T_("After %d uploads"), $expVal);
    }
    else
    {
      $expVal = $DATA["grant_last_time"];
      return sprintf(T_("%s after next upload"), humanTime($expVal));
    }
  }
  elseif($DATA["grant_expire"])
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

  if(!isset($params["grant_expiry"]))
  {
    if(!isset($params["grant_total"]) && !isset($params["grant_lastul"]) && !isset($params["grant_maxul"]))
      $params["grant_expiry"] = "auto";
    else
      $params["grant_expiry"] = "custom";
  }
  if($params["grant_expiry"] === "never")
  {
    $total = "NULL";
    $lastul = "NULL";
    $maxul = "NULL";
  }
  elseif($params["grant_expiry"] === "once")
  {
    $total = ($defaults['grant']['total'] == 0)? "NULL": $defaults['grant']['total'];
    $lastul = "NULL";
    $maxul = 1;
  }
  elseif($params["grant_expiry"] === "auto")
  {
    $total = ($defaults['grant']['total'] == 0)? "NULL": $defaults['grant']['total'];
    $lastul = ($defaults['grant']['lastul'] == 0)? "NULL": $defaults['grant']['lastul'];
    $maxul = ($defaults['grant']['maxul'] == 0)? "NULL": $defaults['grant']['maxul'];
  }
  else
  {
    $total = (empty($params["grant_total"])? 'NULL': $params["grant_total"]);
    $lastul = (empty($params["grant_lastul"])? 'NULL': (int)$params["grant_lastul"]);
    $maxul = (empty($params["grant_maxul"])? 'NULL': (int)$params["grant_maxul"]);
  }

  return array($total, $lastul, $maxul);
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
  list($grant_total, $grant_lastul, $grant_maxul) = grantExpirationParams($params);
  list($ticket_total, $ticket_lastdl, $ticket_maxdl) = ticketExpirationParams($params);

  // prepare data
  $sql = "INSERT INTO \"grant\" (id, user_id, grant_expire, grant_last_time"
       . ", grant_expire_uln, cmt, pass_ph, time, expire, last_time, expire_dln"
       . ", notify_email, sent_email, locale) VALUES (";
  $sql .= $db->quote($id);
  $sql .= ", " . $auth['id'];
  $sql .= ", " . $grant_total;
  $sql .= ", " . $grant_lastul;
  $sql .= ", " . $grant_maxul;
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
  'grant_lastul'     => 'is_numeric_int',
  'grant_maxul'      => 'is_numeric_int',
  'grant_expiry'     => 'is_expiry_choice',
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
  'grant_lastuldays'  => 'is_numeric',
  'grant_maxul'       => 'is_numeric_int',
  'grant_expiry'      => 'is_expiry_choice',
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
