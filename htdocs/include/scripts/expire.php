<?php
if(!isset($argc)) die("not running from the command line\n");
include("../init.php");
if($gcInternal === false) runGc();
?>
