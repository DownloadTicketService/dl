<?php
// initialize the session and locale
include("init.php");

// expire tickets before serving any request
if($gcInternal === true
&& ($gcProbability === 1.
 || (mt_rand() / mt_getrandmax() < $gcProbability)))
  runGc();

// start the session
session_name($sessionName);
session_start();
$auth = &$_SESSION["auth"];

// set session's locale
$locale = &$_SESSION["locale"];
$locale = detectLocale($locale);
switchLocale($locale);
?>
