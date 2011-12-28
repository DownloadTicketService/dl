#!/usr/bin/env php
<?php
if(!isset($argc)) die("not running from the command line\n");
require_once("../init.php");
require_once("admfuncs.php");
if($gcInternal === false) runGc();
?>
