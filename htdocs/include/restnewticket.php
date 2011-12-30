<?php
// REST "newticket" request
require_once("ticketfuncs.php");

function newticket($msg, $params = null)
{
  global $ticketNewParams;

  // handle the upload itself
  $DATA = $validated = false;
  if(isset($_FILES["file"])
  && is_uploaded_file($_FILES["file"]["tmp_name"])
  && $_FILES["file"]["error"] == UPLOAD_ERR_OK
  && ($validated = validateParams($ticketNewParams, $msg)))
    $DATA = handleUpload($_FILES["file"], $msg);

  if($DATA === false)
  {
    // ticket creation unsucessfull
    if($validated && !empty($_FILES["file"]) && !empty($_FILES["file"]["name"]))
      return array("error" => uploadErrorStr($_FILES["file"]));
    else
      return array("error" => "bad parameters");
  }

  // return ticket instance
  return array
  (
    "id"  => $DATA['id'],
    "url" => ticketUrl($DATA),
  );
}

?>
