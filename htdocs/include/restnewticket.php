<?php
// REST "newticket" request
require_once("ticketfuncs.php");

function newticket($msg, $params = null)
{
  global $ticketRestParams;

  // handle the upload itself
  $DATA = $validated = false;
  $FILES = uploadedFiles($_FILES["file"]);
  if($FILES !== false && ($validated = validateParams($ticketRestParams, $msg)))
    $DATA = withUpload($FILES, 'genTicket', $msg);

  if($DATA === false)
  {
    // ticket creation unsucessfull
    if($UPLOAD_ERRNO != UPLOAD_ERR_OK)
    {
      $err = uploadErrorStr();
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
      // errors already generated in newTicket
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
