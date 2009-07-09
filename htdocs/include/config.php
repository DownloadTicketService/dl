<?php
// user config
$phpExt = ".php"; // you can use "" if you enable apache's MultiViews option
$masterPath = "http://dl.example.com/";
$dbHandler = "db4"; // change to db3/db2/dbm depending on your php version
$maxSize = ini_get('upload_max_filesize');
$fromAddr = "ticket service <nobody@example.com>";
$masterPass = "change me";
$spoolDir = "/var/spool/dl/";
?>
