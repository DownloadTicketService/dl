<?php
// process a grant request
require_once("grantfuncs.php");

// handle the request
$DATA = false;
if(validateParams($grantNewParams, $_POST))
{
  // uniform the request parameters to the REST api
  if(isset($_POST['grant_totaldays']))
    $_POST['grant_total'] = (int)($_POST['grant_totaldays'] * 3600 * 24);
  if(isset($_POST['grant_lastuldays']))
    $_POST['grant_lastul'] = (int)($_POST['grant_lastuldays'] * 3600 * 24);
  if(isset($_POST['ticket_totaldays']))
    $_POST['ticket_total'] = (int)($_POST['ticket_totaldays'] * 3600 * 24);
  if(isset($_POST['ticket_lastdldays']))
    $_POST['ticket_lastdl'] = (int)($_POST['ticket_lastdldays'] * 3600 * 24);

  $DATA = genGrant($_POST);
}

// resulting page
if($DATA !== false)
  include("newgrantr.php");
else
  include("newgrants.php");
?>
