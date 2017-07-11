<?php
// process a ticket
require_once("ticketfuncs.php");

// try to fetch the ticket
$id = $_REQUEST["t"];
if(!isTicketId($id))
{
  $id = false;
  $DATA = false;
}
else
{
  $sql = "SELECT * FROM ticket WHERE id = " . $db->quote($id);
  $DATA = $db->query($sql)->fetch();
}

$ref = "$masterPath?t=$id";
if($DATA === false || isTicketExpired($DATA))
{
  includeTemplate("$style/include/noticket.php", array('id' => $id));
  exit();
}

// check for password
if(hasPassHash($DATA) && !isset($_SESSION['t'][$id]))
{
  if(!empty($_POST['p']) && checkPassHash('ticket', $DATA, $_POST['p']))
  {
    // authorize the ticket for this session
    $_SESSION['t'][$id] = array('pass' => $_POST["p"]);
  }
  else
  {
    include("ticketp.php");
    exit();
  }
}

// fix IE total crap by moving to a new location containing the resulting file
// name in the URL (this could be improved for browsers known to work by
// starting to send the file immediately)
header("Location: $dPath/$id/" . rawurlencode($DATA["name"]));
