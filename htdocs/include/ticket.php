<?php
// process a ticket

// try to fetch the ticket
$id = $_REQUEST["t"];
$sql = "SELECT * FROM ticket WHERE id = " . $db->quote($id);
$DATA = $db->query($sql)->fetch();
if($DATA === false)
{
  includeTemplate("style/include/noticket.php",
      array('title' => 'Unknown ticket', 'id' => $id));
  exit();
}

// check for password
if(isset($DATA['pass_md5']))
{
  $pass = (empty($_REQUEST["p"])? false: md5($_REQUEST["p"]));
  if($pass === $DATA['pass_md5'])
  {
    // authorize the ticket for this session
    $_SESSION['t'][$id] = $pass;
  }
  else
  {
    include("include/password.php");
    exit();
  }
}

// fix IE total crap by moving to a new location containing the resulting file
// name in the URL (this could be improved for browsers known to work by
// starting to send the file immediately)
header("Location: $dPath/$id/" . urlencode($DATA["name"]));
?>
