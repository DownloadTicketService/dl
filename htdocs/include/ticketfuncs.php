<?php
// new ticket shared functions
require_once("funcs.php");

function isTicketExpired($DATA, $now = NULL)
{
  if(!isset($now)) $now = time();
  return (($DATA["expire"] && ($DATA["expire"] + $DATA["time"]) < $now)
       || ($DATA["last_stamp"] && $DATA["last_time"] && ($DATA["last_stamp"] + $DATA["last_time"]) < $now)
       || ($DATA["expire_dln"] && $DATA["expire_dln"] <= $DATA["downloads"]));
}


function ticketExpiration($DATA, &$expVal = NULL)
{
  if($DATA["expire_dln"] || $DATA["last_time"])
  {
    if($DATA["last_stamp"] && $DATA["last_time"])
    {
      $expVal = $DATA["last_stamp"] + $DATA["last_time"] - time();
      return sprintf(T_("About %s"), humanTime($expVal));
    }
    elseif($DATA["expire_dln"] && $DATA["downloads"])
    {
      $expVal = ($DATA["expire_dln"] - $DATA["downloads"]);
      return sprintf(T_("About %d downloads"), $expVal);
    }
    elseif($DATA["expire"])
    {
      $expVal = $DATA["expire"] + $DATA["time"] - time();
      return sprintf(T_("About %s"), humanTime($expVal));
    }
    elseif($DATA["expire_dln"])
    {
      $expVal = $DATA["expire_dln"];
      return sprintf(T_("After %d downloads"), $expVal);
    }
    else
    {
      $expVal = $DATA["last_time"];
      return sprintf(T_("%s after next download"), humanTime($expVal));
    }
  }
  elseif($DATA["expire"])
  {
    $expVal = $DATA["expire"] + $DATA["time"] - time();
    return sprintf(T_("In %s"), humanTime($expVal));
  }

  $expVal = 4294967295;
  return ("<strong>" . T_("Never") . "</strong>");
}


function ticketExpirationParams($params)
{
  global $defaults;

  if(!isset($params["ticket_expiry"]))
  {
    if(to_boolean(@$params["ticket_permanent"]) === true
    || to_boolean(@$params["permanent"]) === true)
      $params["ticket_expiry"] = "never"; // dl < 0.18
    elseif(!isset($params["ticket_total"]) && !isset($params["ticket_lastdl"]) && !isset($params["ticket_maxdl"]))
      $params["ticket_expiry"] = "auto";
    else
      $params["ticket_expiry"] = "custom";
  }
  if($params["ticket_expiry"] === "never")
  {
    $total = "NULL";
    $lastdl = "NULL";
    $maxdl = "NULL";
  }
  elseif($params["ticket_expiry"] === "once")
  {
    $total = ($defaults['ticket']['total'] == 0)? "NULL": $defaults['ticket']['total'];
    $lastdl = "NULL";
    $maxdl = 1;
  }
  elseif($params["ticket_expiry"] === "auto")
  {
    $total = ($defaults['ticket']['total'] == 0)? "NULL": $defaults['ticket']['total'];
    $lastdl = ($defaults['ticket']['lastdl'] == 0)? "NULL": $defaults['ticket']['lastdl'];
    $maxdl = ($defaults['ticket']['maxdl'] == 0)? "NULL": $defaults['ticket']['maxdl'];
  }
  else
  {
    $total = (empty($params["ticket_total"])? 'NULL': $params["ticket_total"]);
    $lastdl = (empty($params["ticket_lastdl"])? 'NULL': (int)$params["ticket_lastdl"]);
    $maxdl = (empty($params["ticket_maxdl"])? 'NULL': (int)$params["ticket_maxdl"]);
  }

  return array($total, $lastdl, $maxdl);
}


function genTicket($upload, $params)
{
  global $auth, $locale;

  // populate comment with file list when empty
  if(!empty($params["comment"]))
    $params["comment"] = trim($params["comment"]);
  if(empty($params["comment"]) && count($upload['files']) > 1)
    $params["comment"] = T_("Archive contents:") . "\n  " . implode("\n  ", $upload['files']);

  // expiration values
  list($total, $lastdl, $maxdl) = ticketExpirationParams($params);

  $success = DBConnection::getInstance()->getGenTicketQuery()->setParameter(0,$upload['id'])->
                                                              setParameter(1,$auth['id'])->
                                                              setParameter(2,$upload['name'])->
                                                              setParameter(3,$upload['path'])->
                                                              setParameter(4,$upload['size'])->
                                                              setParameter(5,$params["comment"])->
                                                              setParameter(6, (empty($params["pass"]) ? NULL : hashPassword($params["pass"])))->
                                                              setParameter(7,$params["pass_send"])->
                                                              setParameter(8,time())->
                                                              setParameter(9,$total)->
                                                              setParameter(10,$lastdl)->
                                                              setParameter(11,$maxdl)->
                                                              setParameter(12,(empty($params["notify"])? NULL : fixEMailAddrs($params["notify"])))->
                                                              setParameter(13,(empty($params["send_to"])? NULL : fixEMailAddrs($params["send_to"])))->
                                                              setParameter(14,$locale)->execute();
 $DATA = DBConnection::getInstance()->getTicketById($upload['id']);
 $DATA['pass'] = (empty($params["pass"])? NULL : $params["pass"]);
 
 Hooks::getInstance()->callHook('onTicketCreate',$DATA);

 return $DATA;
}


// parameters validation
$ticketRestParams = array
(
  'comment'          => 'is_string',
  'pass'             => 'is_string',
  'pass_send'        => 'is_boolean',
  'ticket_total'     => 'is_numeric_int',
  'ticket_lastdl'    => 'is_numeric_int',
  'ticket_maxdl'     => 'is_numeric_int',
  'ticket_expiry'    => 'is_expiry_choice',
  'ticket_permanent' => 'is_boolean',
  'notify'           => 'is_email_list',
  'send_to'          => 'is_email_list',
  'permanent'        => 'is_boolean',
);

$ticketNewParams = array
(
  'comment'           => 'is_string',
  'pass'              => 'is_string',
  'pass_send'         => 'is_boolean',
  'ticket_totaldays'  => 'is_numeric',
  'ticket_lastdldays' => 'is_numeric',
  'ticket_maxdl'      => 'is_numeric_int',
  'ticket_expiry'     => 'is_expiry_choice',
  'ticket_permanent'  => false,
  'notify'            => 'is_email_list',
  'send_to'           => 'is_email_list',
  'permanent'         => false,
);

$ticketEditParams = $ticketNewParams;
$ticketEditParams['pass_clear'] = 'is_boolean';
$ticketEditParams['ticket_permanent'] = 'is_boolean';
$ticketEditParams['name'] = array
(
  'required' => true,
  'funcs'    => array('is_string', 'not_empty'),
);
