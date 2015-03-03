<?php
// REST "newticket" request
require_once("ticketfuncs.php");

function newticket($msg, $params = null)
{
  global $ticketRestParams;

  // handle the upload itself
  $DATA = $validated = false;
  if(isset($_FILES["file"])
  && is_uploaded_file($_FILES["file"]["tmp_name"])
  && $_FILES["file"]["error"] == UPLOAD_ERR_OK
  && ($validated = validateParams($ticketRestParams, $msg)))
    $DATA = handleUpload($_FILES["file"], $msg);

  if($DATA === false)
  {
    // ticket creation unsucessfull
    if($validated && !empty($_FILES["file"]) && !empty($_FILES["file"]["name"]))
    {
      $err = uploadErrorStr($_FILES["file"]);
      logError("ticket upload failure: $err");
      return array('httpInternalError', $err);
    }
    elseif(!$validated)
    {
      logError('invalid ticket parameters');
      return array('httpBadRequest', 'bad parameters');
    }
    else
    {
      // errors already generated in handleUpload
      return array('httpInternalError', 'internal error');
    }
  }

  // return ticket instance
  return array(false, array
  (
    "id"  => $DATA['id'],
    "url" => ticketUrl($DATA),
  ));
}

?>
