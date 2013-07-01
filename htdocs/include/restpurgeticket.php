<?php
// REST "purgeticket" request
require_once("ticketfuncs.php");

function purgeticket($msg, $id = null)
{
  global $db, $auth;

  // check id validity
  if(empty($id) || !isTicketId($id))
    return array('httpBadRequest', 'bad parameters');

  // fetch the ticket id
  $sql = "SELECT * FROM ticket WHERE id = " . $db->quote($id);
  $DATA = $db->query($sql)->fetch();
  if($DATA === false || isTicketExpired($DATA))
    return array('httpNotFound', 'not found');

  // check for permissions
  if(!$auth["admin"] && $DATA["user_id"] != $auth["id"])
    return array('httpUnauthorized', 'not authorized');

  // actually purge the ticket
  ticketPurge($DATA, false);
  return array(false, false);
}

?>
