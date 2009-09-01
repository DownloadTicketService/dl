<?php
// download ticket system
include("include/init.php");

if(!empty($_REQUEST["t"]))
  include("include/ticket.php");
else
  header("Location: $adminPath");
?>
