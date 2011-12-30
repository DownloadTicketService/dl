<?php
// process a file submission
require_once("ticketfuncs.php");

// handle the request
$DATA = false;
if(isset($_FILES["file"])
&& is_uploaded_file($_FILES["file"]["tmp_name"])
&& $_FILES["file"]["error"] == UPLOAD_ERR_OK
&& validateParams($ticketNewParams, $_POST))
  $DATA = handleUpload($_FILES["file"], $_POST);

// resulting page
if($DATA !== false)
  include("newticketr.php");
else
  include("newtickets.php");
?>
