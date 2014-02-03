<?php
// download ticket system
include("include/sesslang.php");

if(isset($_REQUEST["t"]))
  include("include/ticket.php");
elseif(isset($_REQUEST["g"]))
  include("include/grant.php");
else
  header("Location: $adminPath");
?>
