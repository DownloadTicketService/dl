<?php
// process a file submission
require_once("ticketfuncs.php");

// handle the request
$DATA = false;
if(isset($_FILES["file"])
&& is_uploaded_file($_FILES["file"]["tmp_name"])
&& $_FILES["file"]["error"] == UPLOAD_ERR_OK
&& validateParams($ticketNewParams, $_POST))
{
  // uniform the request parameters to the REST api
  if(isset($_POST['ticket_totaldays']))
    $_POST['ticket_total'] = (int)($_POST['ticket_totaldays'] * 3600 * 24);
  if(isset($_POST['ticket_lastdldays']))
    $_POST['ticket_lastdl'] = (int)($_POST['ticket_lastdldays'] * 3600 * 24);
  if(isset($_POST['ticket_permanent']))
    $_POST['permanent'] = !empty($_POST['ticket_permanent']);

  $DATA = handleUpload($_FILES["file"], $_POST);
}

// resulting page
if($DATA !== false)
  include("newticketr.php");
else
  include("newtickets.php");
?>
