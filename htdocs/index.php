<?php
// download ticket system
include("include/init.php");

if(!empty($_REQUEST["t"]))
  include("include/ticket.php");
elseif(!empty($_REQUEST["g"]))
  include("include/grant.php");
else
  header("Location: $adminPath");
?>
