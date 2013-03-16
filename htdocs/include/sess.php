<?php
// initialize the session
include("init.php");
require_once("admfuncs.php");

// expire tickets before serving any request
if($gcInternal === true
&& ($gcProbability === 1.
 || (mt_rand() / mt_getrandmax() < $gcProbability)))
  runGc();

// start the session and session-global variables
ini_set('session.use_cookies', 1);
ini_set("session.use_only_cookies", 1);
session_name($sessionName);
session_start();
$auth = &$_SESSION["auth"];
if(!isset($_SESSION["started"]))
{
  session_regenerate_id();
  $_SESSION["started"] = true;
}
?>
