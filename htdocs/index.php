<?php
// download ticket system
include("include/init.php");

if(isset($_REQUEST["t"]))
  include("include/ticket.php");
elseif(isset($_REQUEST["g"]))
  include("include/grant.php");
elseif(isset($_REQUEST["s"]))
  include("include/status.php");
else
  header("Location: $adminPath");
?>
