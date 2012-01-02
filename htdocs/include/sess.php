<?php
// initialize the session and locale
include("init.php");
require_once("admfuncs.php");

// expire tickets before serving any request
if($gcInternal === true
&& ($gcProbability === 1.
 || (mt_rand() / mt_getrandmax() < $gcProbability)))
  runGc();

// start the session and session-global variables
session_name($sessionName);
session_start();
$auth = &$_SESSION["auth"];
?>
